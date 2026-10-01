<?php
// NOVO: lista solicitações e registra a decisão do administrador conectado.
require_once __DIR__ . '/includes/site/auth.php';
require_once __DIR__ . '/includes/site/professores.php';
header('Cache-Control: no-store');
if (!$autenticado) { header('Location: login.php', true, 303); exit; }
$permitido = ($contaAtual['tipo_usuario'] ?? '') === 'administrador';
$erroPedidos = '';
$avisoPedidos = $_SESSION['resposta_professor'] ?? '';
unset($_SESSION['resposta_professor']);
$filtro = is_string($_GET['status'] ?? null) ? $_GET['status'] : 'pendente';
if (!in_array($filtro, ['pendente', 'aprovada', 'recusada'], true)) $filtro = 'pendente';
$pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100000]]) ?: 1;
$pedidos = [];
$total = 0;
if (!$permitido) { http_response_code(403); }
else {
    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!auth_csrf_valido()) { http_response_code(403); throw new RuntimeException('Solicitação expirada. Atualize a página e tente novamente.'); }
            $id = filter_input(INPUT_POST, 'pedido', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (!$id) throw new RuntimeException('Solicitação inválida.');
            $decisao = is_string($_POST['decisao'] ?? null) ? $_POST['decisao'] : '';
            $observacao = is_string($_POST['observacao'] ?? null) ? trim($_POST['observacao']) : '';
            professor_responder($pdo, (int) $contaAtual['id_usuario'], $id, $decisao, $observacao);
            $_SESSION['resposta_professor'] = $decisao === 'aprovada' ? 'Solicitação aprovada. A conta agora é de professor.' : 'Solicitação recusada. A conta continua com a permissão anterior.';
            header('Location: solicitacoes-professor.php', true, 303); exit;
        }
    } catch (PDOException $e) {
        error_log('[EnsinoTec] Falha ao responder solicitação. Código: ' . $e->getCode());
        $erroPedidos = 'Não foi possível salvar a resposta. Tente novamente mais tarde.';
    } catch (RuntimeException $e) { $erroPedidos = $e->getMessage(); }
    try {
        $q = $pdo->prepare('SELECT COUNT(*) FROM solicitacao_professor WHERE status = ?');
        $q->execute([$filtro]);
        $total = (int) $q->fetchColumn();
        $pagina = min($pagina, max(1, (int) ceil($total / 20)));
        $q = $pdo->prepare('SELECT s.*, u.nome, u.email, u.tipo_usuario, u.ativo, a.nome AS administrador_nome FROM solicitacao_professor s JOIN usuario u ON u.id_usuario = s.id_usuario LEFT JOIN usuario a ON a.id_usuario = s.id_administrador WHERE s.status = ? ORDER BY s.data_solicitacao DESC, s.id_solicitacao DESC LIMIT 20 OFFSET ?');
        $q->bindValue(1, $filtro);
        $q->bindValue(2, ($pagina - 1) * 20, PDO::PARAM_INT);
        $q->execute();
        $pedidos = $q->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { $erroPedidos = 'Não foi possível carregar as solicitações agora.'; }
}
$tituloPagina = 'Solicitações de professor — EnsinoTec';
$estilosPagina = ['assets/css/administracao.css?v=2', 'assets/css/professores.css'];
require __DIR__ . '/includes/site/header.php';
?>
<main class="administracao site-container" id="conteudo-principal" tabindex="-1">
<?php if (!$permitido): ?>
    <h1>Acesso restrito</h1><p>Somente administradores podem analisar solicitações.</p><a class="admin-link" href="index.php">Voltar ao site</a>
<?php else: ?>
    <a class="admin-link" href="administracao.php">← Voltar à administração</a>
    <h1>Solicitações de professor</h1>
    <p class="admin-descricao">A aprovação libera o acesso de professor. A resposta também aparece no perfil do solicitante.</p>
    <?php if ($erroPedidos): ?><p class="pedido-aviso" role="alert"><?= site_escape($erroPedidos) ?></p><?php endif; ?>
    <?php if ($avisoPedidos): ?><p class="pedido-aviso" role="status"><?= site_escape($avisoPedidos) ?></p><?php endif; ?>
    <nav class="pedido-filtros" aria-label="Filtrar solicitações">
        <?php foreach (['pendente' => 'Pendentes', 'aprovada' => 'Aprovadas', 'recusada' => 'Recusadas'] as $valor => $rotulo): ?>
        <a class="admin-link" href="solicitacoes-professor.php?status=<?= $valor ?>" <?= $filtro === $valor ? 'aria-current="page"' : '' ?>><?= $rotulo ?></a>
        <?php endforeach; ?>
    </nav>
    <p><?= $total ?> solicitação(ões) · Página <?= $pagina ?></p>
    <div class="pedido-lista">
    <?php foreach ($pedidos as $pedido): ?>
        <article class="pedido-card">
            <h2><?= site_escape($pedido['nome']) ?></h2>
            <p><?= site_escape($pedido['email']) ?></p>
            <p class="pedido-data">Enviado em <?= site_escape(date('d/m/Y H:i', strtotime($pedido['data_solicitacao']))) ?></p>
            <p class="pedido-mensagem"><?= $pedido['mensagem'] ? nl2br(site_escape($pedido['mensagem'])) : 'Nenhuma mensagem enviada.' ?></p>
            <?php if ($pedido['status'] === 'pendente'): ?>
            <?php if (!$pedido['ativo'] || $pedido['tipo_usuario'] !== 'aluno'): ?><p>Esta conta não é um aluno ativo. Você pode recusar o pedido.</p><?php endif; ?>
            <form action="solicitacoes-professor.php" method="post" class="pedido-form">
                <input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>">
                <input type="hidden" name="pedido" value="<?= (int) $pedido['id_solicitacao'] ?>">
                <label for="resposta-<?= (int) $pedido['id_solicitacao'] ?>">Resposta ao solicitante (opcional)</label>
                <textarea id="resposta-<?= (int) $pedido['id_solicitacao'] ?>" name="observacao" rows="3" maxlength="2000"></textarea>
                <div class="pedido-acoes">
                    <?php if ($pedido['ativo'] && $pedido['tipo_usuario'] === 'aluno'): ?><button class="pedido-botao" type="submit" name="decisao" value="aprovada">Aprovar</button><?php endif; ?>
                    <button class="pedido-botao pedido-secundario" type="submit" name="decisao" value="recusada">Recusar</button>
                </div>
            </form>
            <?php else: ?>
            <p>Respondido por <?= site_escape($pedido['administrador_nome'] ?? 'Administrador indisponível') ?><?= $pedido['data_resposta'] ? ' em ' . site_escape(date('d/m/Y H:i', strtotime($pedido['data_resposta']))) : '' ?>.</p>
            <?php if ($pedido['observacao_admin']): ?><p class="pedido-resposta"><?= nl2br(site_escape($pedido['observacao_admin'])) ?></p><?php endif; ?>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
    <?php if (!$pedidos && !$erroPedidos): ?><p class="pedido-vazio">Nenhuma solicitação nesta categoria.</p><?php endif; ?>
    </div>
    <nav class="pedido-filtros" aria-label="Páginas de solicitações">
        <?php if ($pagina > 1): ?><a class="admin-link" href="?status=<?= $filtro ?>&amp;pagina=<?= $pagina - 1 ?>">Anterior</a><?php endif; ?>
        <?php if ($pagina * 20 < $total): ?><a class="admin-link" href="?status=<?= $filtro ?>&amp;pagina=<?= $pagina + 1 ?>">Próxima</a><?php endif; ?>
    </nav>
<?php endif; ?>
</main>
<?php require __DIR__ . '/includes/site/footer.php'; ?>
