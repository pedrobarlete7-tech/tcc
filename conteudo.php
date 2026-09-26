<?php
require_once __DIR__ . '/includes/site/init.php';
require_once __DIR__ . '/includes/conteudo/dados.php';

// ALTERADO: exibe a página 404 quando o conteúdo solicitado não existe.
header('Cache-Control: no-store');
$idConteudo = filter_input(INPUT_GET, 'id_conteudo', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$conteudo = null;
$erroConteudo = '';
if (!$idConteudo) {
    require __DIR__ . '/404.php';
    exit;
} else {
    require_once __DIR__ . '/actions/conexao.php';
    try {
        $conteudo = carregar_conteudo($pdo, $idConteudo);
        if (!$conteudo) {
            require __DIR__ . '/404.php';
            exit;
        }
    } catch (PDOException $erro) {
        http_response_code(503);
        error_log('[EnsinoTec] Falha ao carregar conteúdo. Código: ' . $erro->getCode());
        $erroConteudo = 'Não foi possível carregar o conteúdo. Tente novamente mais tarde.';
    }
}
$tituloPagina = ($conteudo['titulo'] ?? 'Conteúdo') . ' — EnsinoTec';
$estilosPagina = ['assets/css/conteudo.css'];
require __DIR__ . '/includes/site/header.php';
?>
<main class="conteudo" id="conteudo-principal" tabindex="-1">
<?php if ($erroConteudo !== ''): ?>
    <h1>Conteúdo</h1>
    <p role="alert"><?= site_escape($erroConteudo) ?></p>
    <p><a href="index.php">Voltar ao início</a></p>
<?php else: ?>
    <h1><?= site_escape($conteudo['titulo']) ?></h1>
    <div class="resumo">
        <h3><?= site_escape($conteudo['materia_titulo']) ?> · Nível <?= site_escape($conteudo['nivel_dificuldade']) ?></h3>
        <?php foreach ($conteudo['resumos'] as $resumo): ?>
            <?php if (!empty($resumo['descricao'])): ?>
            <p><?= nl2br(site_escape($resumo['descricao'])) ?></p>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php foreach ($conteudo['resumos'] as $resumo): ?>
        <?php if (!empty($resumo['caminho_imagem'])): $urlImagem = url_midia($resumo['caminho_imagem']); ?>
        <div class="imagem">
            <?php if ($urlImagem !== null): ?>
            <img src="<?= site_escape($urlImagem) ?>" alt="<?= site_escape($resumo['descricao'] ?: $conteudo['titulo']) ?>" loading="lazy">
            <?php else: ?>
            <p class="midia-indisponivel">Imagem indisponível.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
    <div class="texto-grande">
        <h2>Explicação</h2>
        <p><?= nl2br(site_escape($conteudo['texto'])) ?></p>
    </div>
    <?php if ($conteudo['videos']): ?>
    <div class="video">
        <h2>Vídeo</h2>
        <?php foreach ($conteudo['videos'] as $video): $urlVideo = url_midia($video['url_video']); ?>
        <div class="caixa-video">
            <p><?= site_escape($video['titulo'] ?: 'Vídeo do conteúdo') ?></p>
            <?php if ($urlVideo !== null): ?>
            <a href="<?= site_escape($urlVideo) ?>" target="_blank" rel="noopener noreferrer">Assistir vídeo</a>
            <?php else: ?>
            <p class="midia-indisponivel">Vídeo indisponível.</p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="exercicios">
        <h2>Exercícios</h2>
        <?php foreach ($conteudo['exercicios'] as $exercicio): ?>
        <div class="questao">
            <p><strong><?= nl2br(site_escape($exercicio['pergunta'])) ?></strong></p>
            <?php foreach ($exercicio['imagens'] as $imagem): $urlImagem = url_midia($imagem['caminho_arquivo']); ?>
            <figure class="imagem-questao">
                <?php if ($urlImagem !== null): ?>
                <img src="<?= site_escape($urlImagem) ?>" alt="<?= site_escape($imagem['legenda'] ?: 'Imagem da questão') ?>" loading="lazy">
                <?php else: ?>
                <p class="midia-indisponivel">Imagem da questão indisponível.</p>
                <?php endif; ?>
                <?php if (!empty($imagem['legenda'])): ?><figcaption><?= site_escape($imagem['legenda']) ?></figcaption><?php endif; ?>
            </figure>
            <?php endforeach; ?>
            <ul>
                <?php foreach ($exercicio['alternativas'] as $alternativa): ?>
                <li><?= site_escape($alternativa['texto']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endforeach; ?>
        <?php if (!$conteudo['exercicios']): ?>
        <p class="sem-exercicios">Nenhum exercício disponível para este conteúdo ainda.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>
</main>
<?php require __DIR__ . '/includes/site/footer.php'; ?>
