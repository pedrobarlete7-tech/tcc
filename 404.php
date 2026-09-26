<?php
// NOVO: página de erro compartilhada para endereços e conteúdos inexistentes.
http_response_code(404);
header('Cache-Control: no-store');
$tituloPagina = 'Página não encontrada — EnsinoTec';
$estilosPagina = ['assets/css/erro.css'];
$baseSite = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/404.php')), '/') . '/';
require __DIR__ . '/includes/site/header.php';
?>
<main class="pagina-erro site-container" id="conteudo-principal" tabindex="-1">
    <section class="erro-card" aria-labelledby="erro-titulo">
        <p class="erro-codigo" aria-label="Erro 404">404</p>
        <h1 id="erro-titulo">Essa página não foi encontrada</h1>
        <p class="erro-descricao">O endereço pode estar incorreto ou o conteúdo não está mais disponível. Você pode voltar ao início ou explorar as matérias pelo menu.</p>
        <div class="erro-acoes">
            <a class="erro-inicio" href="index.php">Voltar ao início</a>
            <a class="erro-contato" href="contato.php">Fale conosco</a>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/site/footer.php'; ?>
