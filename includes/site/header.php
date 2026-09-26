<?php

require_once __DIR__ . '/init.php';
// ALTERADO: evita carregar uma versão antiga do menu em cache.
$versaoMenu = filemtime(__DIR__ . '/../../assets/js/materias-navbar.js');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= site_escape($tituloPagina ?? 'EnsinoTec — TCC 2026') ?></title>
    <link rel="stylesheet" href="assets/css/site.css">
    
    <?php foreach ($estilosPagina ?? [] as $estiloPagina): ?>
    <link rel="stylesheet" href="<?= site_escape($estiloPagina) ?>">
    <?php endforeach; ?>
    <script src="assets/js/site.js" defer></script>
    <script src="assets/js/materias-navbar.js?v=<?= (int) $versaoMenu ?>" defer></script>
</head>
<body>
<a class="skip-link" href="#conteudo-principal">Pular para o conteúdo</a>
<?php require __DIR__ . '/navbar.php'; ?>
