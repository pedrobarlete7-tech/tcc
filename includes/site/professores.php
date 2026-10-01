<?php
declare(strict_types=1);
// NOVO: registra pedidos e respostas sem permitir mudanças de permissão pelo aluno.
function professor_solicitar(PDO $pdo, int $usuario, string $mensagem): void
{
    if (mb_strlen($mensagem) > 2000) throw new RuntimeException('Use uma mensagem de até 2.000 caracteres.');
    $pdo->beginTransaction();
    try {
        $q = $pdo->prepare('SELECT tipo_usuario, ativo FROM usuario WHERE id_usuario = ? FOR UPDATE');
        $q->execute([$usuario]);
        $conta = $q->fetch(PDO::FETCH_ASSOC);
        if (!$conta || !$conta['ativo'] || $conta['tipo_usuario'] !== 'aluno') throw new RuntimeException('Apenas contas de aluno ativas podem solicitar acesso.');
        $q = $pdo->prepare("SELECT id_solicitacao FROM solicitacao_professor WHERE id_usuario = ? AND status = 'pendente' LIMIT 1 FOR UPDATE");
        $q->execute([$usuario]);
        if ($q->fetchColumn()) throw new RuntimeException('Você já tem uma solicitação pendente. Aguarde a análise.');
        $q = $pdo->prepare("INSERT INTO solicitacao_professor (id_usuario, mensagem, status) VALUES (?, ?, 'pendente')");
        $q->execute([$usuario, $mensagem]);
        $pdo->commit();
    } catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $e; }
}

function professor_responder(PDO $pdo, int $administrador, int $pedido, string $decisao, string $observacao): void
{
    if (!in_array($decisao, ['aprovada', 'recusada'], true)) throw new RuntimeException('Escolha aprovar ou recusar.');
    if (mb_strlen($observacao) > 2000) throw new RuntimeException('Use uma resposta de até 2.000 caracteres.');
    $pdo->beginTransaction();
    try {
        $q = $pdo->prepare('SELECT tipo_usuario, ativo FROM usuario WHERE id_usuario = ? FOR UPDATE');
        $q->execute([$administrador]);
        $admin = $q->fetch(PDO::FETCH_ASSOC);
        if (!$admin || !$admin['ativo'] || $admin['tipo_usuario'] !== 'administrador') throw new RuntimeException('Apenas administradores podem responder aos pedidos.');
        $q = $pdo->prepare('SELECT id_usuario FROM solicitacao_professor WHERE id_solicitacao = ?');
        $q->execute([$pedido]);
        $aluno = (int) $q->fetchColumn();
        if (!$aluno) throw new RuntimeException('Solicitação não encontrada.');
        $q = $pdo->prepare('SELECT tipo_usuario, ativo FROM usuario WHERE id_usuario = ? FOR UPDATE');
        $q->execute([$aluno]);
        $conta = $q->fetch(PDO::FETCH_ASSOC);
        $q = $pdo->prepare('SELECT status FROM solicitacao_professor WHERE id_solicitacao = ? FOR UPDATE');
        $q->execute([$pedido]);
        if ($q->fetchColumn() !== 'pendente') throw new RuntimeException('Esta solicitação já foi respondida. Atualize a página.');
        if ($decisao === 'aprovada') {
            if (!$conta || !$conta['ativo'] || $conta['tipo_usuario'] !== 'aluno') throw new RuntimeException('Só é possível aprovar uma conta de aluno ativa.');
            $q = $pdo->prepare("UPDATE usuario SET tipo_usuario = 'professor' WHERE id_usuario = ?");
            $q->execute([$aluno]);
        }
        $q = $pdo->prepare('UPDATE solicitacao_professor SET status = ?, id_administrador = ?, data_resposta = CURRENT_TIMESTAMP, observacao_admin = ? WHERE id_solicitacao = ?');
        $q->execute([$decisao, $administrador, $observacao, $pedido]);
        $pdo->commit();
    } catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $e; }
}
