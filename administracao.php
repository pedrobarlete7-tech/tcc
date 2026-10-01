<?php
// ALTERADO: dá acesso à gestão de matérias e conteúdos.
require_once __DIR__ . '/includes/site/auth.php';
header('Cache-Control: no-store');
if (!$autenticado) {
    header('Location: login.php', true, 303);
    exit;
}
$permitido = ($contaAtual['tipo_usuario'] ?? '') === 'administrador';
if (!$permitido) http_response_code(403);
$tituloPagina = 'Administração — EnsinoTec';
$estilosPagina = ['assets/css/administracao.css?v=2', 'assets/css/professores.css'];
$resumo = null;
if ($permitido) {
    try {
        $resumo = [
            'usuarios' => (int) $pdo->query('SELECT COUNT(*) FROM usuario')->fetchColumn(),
            'pendentes' => (int) $pdo->query("SELECT COUNT(*) FROM solicitacao_professor WHERE status = 'pendente'")->fetchColumn(),
        ];
    } catch (PDOException $e) { error_log('[EnsinoTec] Falha no resumo administrativo. Código: ' . $e->getCode()); }
}
require __DIR__ . '/includes/site/header.php';
?>
<main class="administracao site-container" id="conteudo-principal" tabindex="-1">
<?php if (!$permitido): ?>
    <h1>Acesso restrito</h1>
    <p>Esta área está disponível apenas para administradores.</p>
    <a class="admin-link" href="index.php">Voltar ao início</a>
<?php else: ?>
    <header class="admin-intro">
    <p class="admin-etiqueta">EnsinoTec · Administração</p>
    <h1>Olá, <?= site_escape((string) $contaAtual['nome']) ?>!</h1>
    <p class="admin-descricao">Acompanhe o portal e acesse as opções administrativas.</p>
    </header>
    <?php if ($resumo !== null): ?>
    <div class="admin-resumo">
        <article><strong><?= $resumo['usuarios'] ?></strong><span>Contas cadastradas</span></article>
        <article><strong><?= $resumo['pendentes'] ?></strong><span>Solicitações de professor pendentes</span></article>
    </div>
    <?php else: ?><p role="status">Não foi possível carregar os totais agora.</p><?php endif; ?>
    <h2 class="admin-secao-titulo">Ferramentas administrativas</h2>
    <section class="admin-opcoes" aria-label="Opções administrativas">
        <article><span class="admin-etiqueta">Disponível</span><h2>Solicitações de professor</h2><p>Confira os pedidos e aprove ou recuse o acesso de professor.</p><a class="admin-link" href="solicitacoes-professor.php">Analisar solicitações</a></article>
        <article><span class="admin-etiqueta">Em breve</span><h2>Gerenciar usuários</h2><p>Espaço reservado para a gestão das contas do EnsinoTec.</p></article>
        <article><span class="admin-etiqueta">Disponível</span><h2>Matérias e conteúdos</h2><p>Gerencie disciplinas, textos, vídeos, imagens e questões do portal.</p><a class="admin-link" href="gerenciar-materias.php">Matérias e conteúdos</a></article>
    </section>
    <div class="admin-acoes"><a class="admin-link" href="index.php">Voltar ao site</a><a class="admin-link" href="configuracoes.php">Configurações da minha conta</a></div>
<?php endif; ?>
</main>
<?php require __DIR__ . '/includes/site/footer.php'; ?>

