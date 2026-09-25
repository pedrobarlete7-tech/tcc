<?php
require_once __DIR__ . '/init.php';
// ALTERADO: a navbar usa a lista enviada em materias-navbar.js.
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NIVELAR — TCC 2026</title>
    <link rel="stylesheet" href="assets/css/site.css">
    <script src="assets/js/site.js" defer></script>
    <!-- NOVO: carrega disciplinas, assuntos e tópicos na navbar. -->
    <script src="assets/js/materias-navbar.js" defer></script>
</head>
<body>
<a class="skip-link" href="#conteudo-principal">Pular para o conteúdo</a>
<?php require __DIR__ . '/navbar.php'; ?>
