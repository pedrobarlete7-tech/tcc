<?php
declare(strict_types=1);
// ALTERADO: mantém os fluxos por e-mail indisponíveis até a configuração do envio na hospedagem.
function seguranca_conta(PDO $pdo, int $id): ?array
{
    $q = $pdo->prepare('SELECT u.*, COALESCE(s.dois_fatores, 0) AS dois_fatores, COALESCE(s.versao, 1) AS versao FROM usuario u LEFT JOIN usuario_seguranca s ON s.id_usuario = u.id_usuario WHERE u.id_usuario = ? AND u.ativo = 1');
    $q->execute([$id]);
    return $q->fetch(PDO::FETCH_ASSOC) ?: null;
}

function seguranca_email_disponivel(): bool
{
    return false;
}

function seguranca_email(string $email, string $codigo, string $finalidade): void
{
    throw new RuntimeException('O envio de e-mails está temporariamente indisponível.');
}

function seguranca_emitir(PDO $pdo, array $conta, string $finalidade): string
{
    if (!seguranca_email_disponivel()) throw new RuntimeException('O envio de e-mails está temporariamente indisponível.');
    $idUsuario = (int) $conta['id_usuario'];
    $pdo->beginTransaction();
    try {
        $q = $pdo->prepare('SELECT id_usuario FROM usuario WHERE id_usuario = ? FOR UPDATE');
        $q->execute([$idUsuario]);
        $atual = seguranca_conta($pdo, $idUsuario);
        if (!$atual) throw new RuntimeException('Conta indisponível.');
        $q = $pdo->prepare('SELECT MAX(criado_em) FROM codigo_seguranca WHERE id_usuario = ? AND finalidade = ?');
        $q->execute([$idUsuario, $finalidade]);
        if ((int) $q->fetchColumn() > time() - 60) throw new RuntimeException('Aguarde um minuto antes de solicitar outro código.');
        $q = $pdo->prepare('UPDATE codigo_seguranca SET usado = 1 WHERE id_usuario = ? AND finalidade = ?');
        $q->execute([$idUsuario, $finalidade]);
        $id = bin2hex(random_bytes(32));
        $codigo = (string) random_int(10000000, 99999999);
        $q = $pdo->prepare('INSERT INTO codigo_seguranca (id, id_usuario, finalidade, codigo_hash, versao, criado_em, expira_em) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $q->execute([$id, $idUsuario, $finalidade, password_hash($codigo, PASSWORD_BCRYPT), $atual['versao'], time(), time() + 600]);
        seguranca_email($atual['email'], $codigo, $finalidade);
        $pdo->commit();
        return $id;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

function seguranca_conferir(PDO $pdo, string $id, string $codigo, string $finalidade): ?array
{
    $pdo->beginTransaction();
    try {
        $q = $pdo->prepare('SELECT * FROM codigo_seguranca WHERE id = ? AND finalidade = ? FOR UPDATE');
        $q->execute([$id, $finalidade]);
        $registro = $q->fetch(PDO::FETCH_ASSOC);
        if (!$registro || $registro['usado'] || $registro['tentativas'] >= 5 || $registro['expira_em'] < time()) { $pdo->commit(); return null; }
        $conta = seguranca_conta($pdo, (int) $registro['id_usuario']);
        $valido = $conta && (int) $conta['versao'] === (int) $registro['versao'] && preg_match('/^[0-9]{8}$/D', $codigo) && password_verify($codigo, $registro['codigo_hash']);
        $q = $pdo->prepare('UPDATE codigo_seguranca SET tentativas = tentativas + 1, usado = ? WHERE id = ?');
        $q->execute([$valido ? 1 : 0, $id]);
        $pdo->commit();
        return $valido ? $conta : null;
    } catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $e; }
}

function seguranca_alterar(PDO $pdo, int $id, int $versao, ?string $senha, ?int $fator): void
{
    $pdo->beginTransaction();
    try {
        $q = $pdo->prepare('SELECT id_usuario FROM usuario WHERE id_usuario = ? FOR UPDATE');
        $q->execute([$id]);
        $conta = seguranca_conta($pdo, $id);
        if (!$conta || (int) $conta['versao'] !== $versao) throw new RuntimeException('Solicitação expirada. Comece novamente.');
        $q = $pdo->prepare('INSERT IGNORE INTO usuario_seguranca (id_usuario) VALUES (?)');
        $q->execute([$id]);
        if ($senha !== null) {
            $q = $pdo->prepare('UPDATE usuario SET senha = ? WHERE id_usuario = ?');
            $q->execute([password_hash($senha, PASSWORD_BCRYPT), $id]);
        }
        $q = $pdo->prepare('UPDATE usuario_seguranca SET versao = versao + 1, dois_fatores = ? WHERE id_usuario = ?');
        $q->execute([$fator ?? (int) $conta['dois_fatores'], $id]);
        $q = $pdo->prepare('DELETE FROM codigo_seguranca WHERE id_usuario = ?');
        $q->execute([$id]);
        $pdo->commit();
    } catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $e; }
}
