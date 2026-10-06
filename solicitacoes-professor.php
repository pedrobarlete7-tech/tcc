<?php
// ALTERADO: pesquisa por nome ou e-mail e mantém a busca nos filtros e ações.
require_once __DIR__ . '/includes/site/auth.php';
require_once __DIR__ . '/includes/site/professores.php';
header('Cache-Control: no-store');
if (!$autenticado) {
    header('Location: login.php', true, 303);
    exit;
}
$permitido = ($contaAtual['tipo_usuario'] ?? '') === 'administrador';
$erroPedidos = '';
$avisoPedidos = $_SESSION['resposta_professor'] ?? '';
unset($_SESSION['resposta_professor']);
$filtro = is_string($_GET['status'] ?? null) ? $_GET['status'] : 'pendente';
if (!in_array($filtro, ['pendente', 'aprovada', 'recusada'], true)) $filtro = 'pendente';
$pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100000]]) ?: 1;
$desfazer = filter_input(INPUT_GET, 'desfazer', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$busca = is_string($_GET['busca'] ?? null) ? mb_substr(trim($_GET['busca']), 0, 150) : '';
$urlPedidos = static function (array $parametros = []) use ($filtro, $busca): string {
    return 'solicitacoes-professor.php?' . http_build_query(array_merge(['status' => $filtro, 'busca' => $busca], $parametros));
};
$pedidos = [];
$total = 0;
if (!$permitido) {
    http_response_code(403);
} else {
    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!auth_csrf_valido()) {
                http_response_code(403);
                throw new RuntimeException('Solicitação expirada. Atualize a página e tente novamente.');
            }
            $id = filter_input(INPUT_POST, 'pedido', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (!$id) throw new RuntimeException('Solicitação inválida.');
            if (($_POST['acao'] ?? '') === 'desfazer') {
                if (($_POST['confirmar'] ?? '') !== 'sim') throw new RuntimeException('Confirme se deseja desfazer a decisão.');
                $statusAnterior = is_string($_POST['status_anterior'] ?? null) ? $_POST['status_anterior'] : '';
                professor_desfazer($pdo, (int)$contaAtual['id_usuario'], $id, $statusAnterior);
                $_SESSION['resposta_professor'] = 'decisão desfeita com sucesso';
                header('Location: ' . $urlPedidos(['status' => 'pendente']), true, 303);
                exit;
            }
            $decisao = is_string($_POST['decisao'] ?? null) ? $_POST['decisao'] : '';
            $observacao = is_string($_POST['observacao'] ?? null) ? trim($_POST['observacao']) : '';
            professor_responder($pdo, (int) $contaAtual['id_usuario'], $id, $decisao, $observacao);
            $_SESSION['resposta_professor'] = $decisao === 'aprovada' ? 'aceito com sucesso' : 'recusado com sucesso';
            header('Location: ' . $urlPedidos(['status' => $decisao]), true, 303);
            exit;
        }
    } catch (PDOException $e) {
        error_log('[EnsinoTec] Falha ao responder solicitação. Código: ' . $e->getCode());
        $erroPedidos = 'Não foi possível salvar a resposta. Tente novamente mais tarde.';
    } catch (RuntimeException $e) {
        $erroPedidos = $e->getMessage();
    }
    try {
        $condicao = 's.status = ?';
        $parametros = [$filtro];
        if ($busca !== '') {
            $termo = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $busca) . '%';
            $condicao .= " AND (u.nome LIKE ? ESCAPE '!' OR u.email LIKE ? ESCAPE '!')";
            $parametros[] = $termo;
            $parametros[] = $termo;
        }
        $q = $pdo->prepare('SELECT COUNT(*) FROM solicitacao_professor s JOIN usuario u ON u.id_usuario = s.id_usuario WHERE ' . $condicao);
        $q->execute($parametros);
        $total = (int) $q->fetchColumn();
        $pagina = min($pagina, max(1, (int) ceil($total / 20)));
        $q = $pdo->prepare('SELECT s.*, u.nome, u.email, u.tipo_usuario, u.ativo, a.nome AS administrador_nome FROM solicitacao_professor s JOIN usuario u ON u.id_usuario = s.id_usuario LEFT JOIN usuario a ON a.id_usuario = s.id_administrador WHERE ' . $condicao . ' ORDER BY s.data_solicitacao DESC, s.id_solicitacao DESC LIMIT 20 OFFSET ?');
        foreach ($parametros as $indice => $valor) $q->bindValue($indice + 1, $valor, PDO::PARAM_STR);
        $q->bindValue(count($parametros) + 1, ($pagina - 1) * 20, PDO::PARAM_INT);
        $q->execute();
        $pedidos = $q->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $erroPedidos = 'Não foi possível carregar as solicitações agora.';
    }
}
$tituloPagina = 'Solicitações de professor — EnsinoTec';
$estilosPagina = ['assets/css/administracao.css?v=2', 'assets/css/professores.css?v=busca-1'];
require __DIR__ . '/includes/site/header.php';
?>
<main class="administracao site-container" id="conteudo-principal" tabindex="-1">
    <?php if (!$permitido): ?>
        <h1>Acesso restrito</h1>
        <p>Somente administradores podem analisar solicitações.</p><a class="admin-link" href="index.php">Voltar ao site</a>
    <?php else: ?>
        <a class="admin-link" href="administracao.php">← Voltar à administração</a>
        <h1>Solicitações de professor</h1>
        <p class="admin-descricao">A aprovação libera o acesso de professor. A resposta também aparece no perfil do solicitante.</p>
        <?php if ($erroPedidos): ?><p class="pedido-aviso" role="alert"><?= site_escape($erroPedidos) ?></p><?php endif; ?>
        <?php if ($avisoPedidos): ?><p class="pedido-aviso" role="status" data-popup-aviso><?= site_escape($avisoPedidos) ?></p><?php endif; ?>
        <form class="pedido-busca" method="get" action="solicitacoes-professor.php" role="search" aria-label="Pesquisar solicitações">
            <input type="hidden" name="status" value="<?= site_escape($filtro) ?>">
            <label for="busca-pedidos">Buscar por nome ou e-mail</label>
            <div class="pedido-busca-campos">
                <input id="busca-pedidos" type="search" name="busca" maxlength="150" placeholder="Digite o nome ou e-mail do solicitante" value="<?= site_escape($busca) ?>">
                <button class="pedido-botao" type="submit">Buscar</button>
                <?php if ($busca !== ''): ?><a class="admin-link" href="<?= site_escape($urlPedidos(['busca' => ''])) ?>">Limpar busca</a><?php endif; ?>
            </div>
        </form>
        <nav class="pedido-filtros" aria-label="Filtrar solicitações">
            <?php foreach (['pendente' => 'Pendentes', 'aprovada' => 'Aprovadas', 'recusada' => 'Recusadas'] as $valor => $rotulo): ?>
                <a class="admin-link" href="<?= site_escape($urlPedidos(['status' => $valor])) ?>" <?= $filtro === $valor ? 'aria-current="page"' : '' ?>><?= $rotulo ?></a>
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
                        <form action="<?= site_escape($urlPedidos()) ?>" method="post" class="pedido-form">
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
                        <?php if ($desfazer === (int)$pedido['id_solicitacao']): ?>
                            <dialog class="pedido-confirmacao" id="confirmar-desfazer" open aria-labelledby="desfazer-titulo-<?= (int)$pedido['id_solicitacao'] ?>" aria-describedby="desfazer-descricao">
                                <h3 id="desfazer-titulo-<?= (int)$pedido['id_solicitacao'] ?>">Desfazer esta decisão?</h3>
                                <p id="desfazer-descricao">A solicitação voltará para Pendentes e poderá ser aprovada ou recusada novamente.<?= $pedido['status'] === 'aprovada' ? ' A conta voltará a ser aluno e perderá o acesso às ferramentas de professor.' : '' ?></p>
                                <?php if ($erroPedidos): ?><p class="pedido-aviso" role="alert"><?= site_escape($erroPedidos) ?></p><?php endif; ?>
                                <form method="post" action="<?= site_escape($urlPedidos(['pagina' => $pagina, 'desfazer' => (int)$pedido['id_solicitacao']])) ?>" class="pedido-form">
                                    <input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>">
                                    <input type="hidden" name="pedido" value="<?= (int)$pedido['id_solicitacao'] ?>">
                                    <input type="hidden" name="acao" value="desfazer">
                                    <input type="hidden" name="status_anterior" value="<?= site_escape($pedido['status']) ?>">
                                    <div class="pedido-acoes"><button type="submit" class="pedido-botao" name="confirmar" value="sim">Sim, desfazer decisão</button><a class="admin-link" data-cancelar-desfazer autofocus href="<?= site_escape($urlPedidos(['pagina' => $pagina])) ?>">Cancelar</a></div>
                                </form>
                            </dialog>
                        <?php else: ?><a class="admin-link" href="<?= site_escape($urlPedidos(['pagina' => $pagina, 'desfazer' => (int)$pedido['id_solicitacao']])) ?>">Desfazer decisão</a><?php endif; ?>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
            <?php if (!$pedidos && !$erroPedidos): ?><p class="pedido-vazio"><?= $busca !== '' ? 'Nenhuma solicitação encontrada para esta busca nesta categoria.' : 'Nenhuma solicitação nesta categoria.' ?></p><?php endif; ?>
        </div>
        <nav class="pedido-filtros" aria-label="Páginas de solicitações">
            <?php if ($pagina > 1): ?><a class="admin-link" href="<?= site_escape($urlPedidos(['pagina' => $pagina - 1])) ?>">Anterior</a><?php endif; ?>
            <?php if ($pagina * 20 < $total): ?><a class="admin-link" href="<?= site_escape($urlPedidos(['pagina' => $pagina + 1])) ?>">Próxima</a><?php endif; ?>
        </nav>
    <?php endif; ?>
</main>
<script src="assets/js/confirmar-professor.js" defer></script>
<?php $popupSucesso = $permitido ? $avisoPedidos : '';
require __DIR__ . '/includes/site/popup-sucesso.php'; ?>
<?php require __DIR__ . '/includes/site/footer.php'; ?>