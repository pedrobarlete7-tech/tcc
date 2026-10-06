<?php

declare(strict_types=1);
// ALTERADO: permite desfazer a última decisão e devolver o pedido para análise.
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
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

function professor_desfazer(PDO $pdo, int $administrador, int $pedido, string $statusAnterior): void
{
    if (!in_array($statusAnterior, ['aprovada', 'recusada'], true)) throw new RuntimeException('Escolha uma solicitação já respondida.');
    $pdo->beginTransaction();
    try {
        $q = $pdo->prepare('SELECT tipo_usuario, ativo FROM usuario WHERE id_usuario=? FOR UPDATE');
        $q->execute([$administrador]);
        $admin = $q->fetch(PDO::FETCH_ASSOC);
        if (!$admin || !$admin['ativo'] || $admin['tipo_usuario'] !== 'administrador') throw new RuntimeException('Apenas administradores podem desfazer decisões.');
        $q = $pdo->prepare('SELECT id_usuario FROM solicitacao_professor WHERE id_solicitacao=?');
        $q->execute([$pedido]);
        $aluno = (int)$q->fetchColumn();
        if (!$aluno) throw new RuntimeException('Solicitação não encontrada.');
        $q = $pdo->prepare('SELECT tipo_usuario, ativo FROM usuario WHERE id_usuario=? FOR UPDATE');
        $q->execute([$aluno]);
        $conta = $q->fetch(PDO::FETCH_ASSOC);
        $q = $pdo->prepare('SELECT status FROM solicitacao_professor WHERE id_solicitacao=? FOR UPDATE');
        $q->execute([$pedido]);
        if ($q->fetchColumn() !== $statusAnterior) throw new RuntimeException('A decisão já mudou. Atualize a página antes de tentar novamente.');
        $q = $pdo->prepare('SELECT id_solicitacao FROM solicitacao_professor WHERE id_usuario=? ORDER BY id_solicitacao DESC LIMIT 1 FOR UPDATE');
        $q->execute([$aluno]);
        if ((int)$q->fetchColumn() !== $pedido) throw new RuntimeException('Há uma solicitação mais recente desta conta. Analise o pedido mais recente.');
        $tipoEsperado = $statusAnterior === 'aprovada' ? 'professor' : 'aluno';
        if (!$conta || !$conta['ativo'] || $conta['tipo_usuario'] !== $tipoEsperado) throw new RuntimeException('A permissão desta conta mudou. Esta decisão não pode ser desfeita.');
        if ($statusAnterior === 'aprovada') {
            $q = $pdo->prepare("SELECT id_solicitacao FROM solicitacao_professor WHERE id_usuario=? AND status='aprovada' AND id_solicitacao<>? LIMIT 1 FOR UPDATE");
            $q->execute([$aluno, $pedido]);
            if ($q->fetchColumn()) throw new RuntimeException('Existe outra aprovação para esta conta. A permissão foi mantida.');
            $q = $pdo->prepare("UPDATE usuario SET tipo_usuario='aluno' WHERE id_usuario=?");
            $q->execute([$aluno]);
        }
        $q = $pdo->prepare("UPDATE solicitacao_professor SET status='pendente', id_administrador=NULL, data_resposta=NULL, observacao_admin=NULL WHERE id_solicitacao=?");
        $q->execute([$pedido]);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
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
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
