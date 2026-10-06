<?php

declare(strict_types=1);
// NOVO: valida alterações de contas e encerra sessões após mudanças de acesso.
function usuarios_admin_salvar(PDO $pdo, int $ator, int $id, string $nome, string $email, string $tipo, int $ativo, string $revisao): void
{
    if ($id < 1 || mb_strlen($nome) < 2 || mb_strlen($nome) > 100) throw new DomainException('Informe um nome de 2 a 100 caracteres.');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) throw new DomainException('Informe um e-mail válido de até 100 caracteres.');
    if (!in_array($tipo, ['aluno', 'professor', 'administrador'], true) || !in_array($ativo, [0, 1], true)) throw new DomainException('Perfil ou situação inválidos.');
    $pdo->beginTransaction();
    try {
        $q = $pdo->prepare('SELECT id_usuario,nome,email,tipo_usuario,ativo FROM usuario WHERE id_usuario IN (?,?) ORDER BY id_usuario FOR UPDATE');
        $q->execute([$ator, $id]);
        $contas = [];
        foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $u) $contas[(int)$u['id_usuario']] = $u;
        $admin = $contas[$ator] ?? null;
        $conta = $contas[$id] ?? null;
        if (!$admin || !(int)$admin['ativo'] || $admin['tipo_usuario'] !== 'administrador') throw new DomainException('Acesso permitido apenas a administradores ativos.');
        if (!$conta) throw new DomainException('Conta não encontrada.');
        if (!hash_equals(usuarios_admin_revisao($conta), $revisao)) throw new DomainException('Esta conta foi alterada. Atualize a página antes de salvar novamente.');
        if ($id === $ator && ($tipo !== 'administrador' || $ativo !== 1)) throw new DomainException('Você não pode retirar seu próprio acesso administrativo.');
        $q = $pdo->prepare('SELECT id_usuario FROM usuario WHERE email=? AND id_usuario<>?');
        $q->execute([$email, $id]);
        if ($q->fetchColumn()) throw new DomainException('Este e-mail já está sendo usado por outra conta.');
        $q = $pdo->prepare('UPDATE usuario SET nome=?,email=?,tipo_usuario=?,ativo=? WHERE id_usuario=?');
        $q->execute([$nome, $email, $tipo, $ativo, $id]);
        if ($conta['email'] !== $email || $conta['tipo_usuario'] !== $tipo || (int)$conta['ativo'] !== $ativo) {
            $q = $pdo->prepare('INSERT INTO usuario_seguranca (id_usuario,versao) VALUES (?,2) ON DUPLICATE KEY UPDATE versao=versao+1');
            $q->execute([$id]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
function usuarios_admin_revisao(array $conta): string
{
    return hash('sha256', json_encode([(int)$conta['id_usuario'], $conta['nome'], $conta['email'], $conta['tipo_usuario'], (int)$conta['ativo']]));
}
