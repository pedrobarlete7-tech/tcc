<?php
// ALTERADO: nome do site padronizado para EnsinoTec.

require_once __DIR__ . '/includes/site/init.php';
$erro = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $erro = 'O acesso às contas ainda não está disponível. Tente novamente quando a integração estiver concluída.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#380d19">
    <title>Entrar — EnsinoTec</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <script src="assets/js/login.js" defer></script>
</head>

<body class="login-page">
    <header class="login-header">
        <a class="login-brand" href="index.php" aria-label="EnsinoTec — início">EnsinoTec</a>
        <a class="back-link" href="index.php">← Voltar ao início</a>
    </header>
    <main class="login-main">
        <section class="login-card" aria-labelledby="login-title">
            <div class="user-emblem" aria-hidden="true"><svg viewBox="0 0 80 80">
                    <circle cx="40" cy="28" r="14" />
                    <path d="M13 70c0-17 10-26 27-26s27 9 27 26" />
                </svg></div>
            <h1 id="login-title">Bem-vindo de volta</h1>
            <p class="login-subtitle">Entre para continuar aprendendo.</p>
            <?php if ($erro !== ''): ?>
                <p class="login-alert" role="alert"><?= site_escape($erro) ?></p>
            <?php endif; ?>
            <form action="login.php" method="post">
                <div class="field">
                    <label for="email">E-mail</label>
                    <div class="input-line">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="14" rx="2" />
                            <path d="m3 6 9 7 9-7" />
                        </svg>
                        <input type="email" id="email" name="email" placeholder="seuemail@exemplo.com" autocomplete="username" maxlength="254" value="<?= site_escape($email) ?>" required>
                    </div>
                </div>
                <div class="field">
                    <label for="senha">Senha</label>
                    <div class="input-line">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="5" y="10" width="14" height="11" rx="2" />
                            <path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3" />
                        </svg>
                        <input type="password" id="senha" name="senha" placeholder="Digite sua senha" autocomplete="current-password" required>
                        <button class="password-toggle" type="button" aria-label="Mostrar senha" aria-controls="senha" aria-pressed="false" hidden><svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg></button>
                    </div>
                </div>
                <div class="login-options">
                    <label class="remember-email" hidden><input type="checkbox" id="lembrar-email"> Lembrar e-mail</label>
                    <details class="password-help">
                        <summary>Esqueceu a senha?</summary>
                        <p>A recuperação de senha estará disponível em breve. Se precisar de ajuda, <a href="contato.php">entre em contato</a>.</p>
                    </details>
                </div>
                <button class="login-submit" type="submit">Entrar <span aria-hidden="true">→</span></button>
            </form>
            <p class="signup-link">Ainda não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
        </section>
    </main>
    <footer class="login-footer">© 2026 EnsinoTec — TCC, Projeto Integrado</footer>
</body>

</html>