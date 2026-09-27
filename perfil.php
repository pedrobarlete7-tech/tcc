<?php
// ALTERADO: mantém o cartão original, integra navbar/footer e mostra a conta do banco.
require_once __DIR__ . '/includes/site/auth.php';
header('Cache-Control: no-store');
if (!$autenticado) {
    header('Location: login.php', true, 303);
    exit;
}
$nomePerfil = (string) $contaAtual['nome'];
$emailPerfil = (string) $contaAtual['email'];
$nomeUsuario = $nomePerfil;
$tituloPagina = 'Meu perfil — EnsinoTec';
$estilosPagina = ['assets/css/perfil.css'];
require __DIR__ . '/includes/site/header.php';
?>
<main class="pagina-perfil" id="conteudo-principal" tabindex="-1">
    <div class="container">
        <a class="btn-voltar" href="index.php">← VOLTAR</a>
        <div class="perfil">
            <div class="foto">
                <?php if (is_file(__DIR__ . '/assets/img/avatar.png')): ?>
                <img src="assets/img/avatar.png" alt="Avatar padrão" width="130" height="130">
                <?php else: ?>
                <svg class="perfil-avatar" viewBox="0 0 80 80" role="img" aria-label="Avatar padrão"><circle cx="40" cy="28" r="14"/><path d="M13 74v-4c0-17 10-26 27-26s27 9 27 26v4Z"/></svg>
                <?php endif; ?>
            </div>
            <div class="informacoes">
                <h1>Meu perfil</h1>
                <div class="campo">
                    <span class="label">Nome</span>
                    <p><?= site_escape($nomePerfil) ?></p>
                </div>
                <div class="campo">
                    <span class="label">E-mail</span>
                    <p><?= site_escape($emailPerfil) ?></p>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require __DIR__ . '/includes/site/footer.php'; ?>
