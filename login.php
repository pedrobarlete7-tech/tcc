<?php
// ALTERADO: integra idioma e progresso preservando as funções atuais.




require_once __DIR__ . '/includes/site/auth.php';
require_once __DIR__ . '/includes/site/login-limite.php';
header('Cache-Control: no-store');
if ($autenticado) {
    header('Location: index.php', true, 303);
    exit;
}
$sucesso = $_SESSION['cadastro_sucesso'] ?? '';
unset($_SESSION['cadastro_sucesso']);
$erro = '';
$email = '';
$manterConectado = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $manterConectado = ($_POST['manter_conectado'] ?? '') === '1';
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $email = strtolower($email);
    $senha = is_string($_POST['senha'] ?? null) ? $_POST['senha'] : '';
    if (!auth_csrf_valido()) {
        $erro = 'Solicitação expirada. Tente novamente.';
    } elseif ($manterConectado && empty($GLOBALS['site_sessao_persistente_disponivel'])) {
        $erro = 'Não foi possível ativar Manter conectado nesta hospedagem. Desmarque a opção para entrar.';
    } elseif (!email_valido($email) || $senha === '' || strlen($senha) > 72 || str_contains($senha, "\0")) {
        $erro = 'E-mail ou senha inválidos.';
    } else {
        require __DIR__ . '/actions/conexao.php';
        try {
            $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'desconhecido');
            $espera = login_limite($email, $ip);
            if ($espera > 0) {
                http_response_code(429);
                header('Retry-After: ' . $espera);
                throw new RuntimeException('Muitas tentativas. Aguarde 5 minutos antes de tentar novamente.');
            }
            if (auth_entrar($pdo, $email, $senha, $manterConectado)) {
                try {
                    login_limite($email, $ip, true);
                } catch (RuntimeException $e) {
                    error_log('[EnsinoTec] Login validado; contador mantido por falha ao limpar.');
                }
                header('Location: ' . (isset($_SESSION['desafio_login']) ? 'verificar-codigo.php' : 'index.php'), true, 303);
                exit;
            }
            $erro = 'E-mail ou senha inválidos.';
        } catch (PDOException $e) {
            error_log('[EnsinoTec] Falha no login. Código: ' . $e->getCode());
            http_response_code(503);
            $erro = 'Não foi possível entrar agora. Tente novamente mais tarde.';
        } catch (RuntimeException $e) {
            $erro = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <script src="assets/js/tema.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#380d19">
    <title data-i18n-message>Entrar — EnsinoTec</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <script src="assets/js/login.js?v=sessao-20261009" defer></script>
    <link rel="stylesheet" href="assets/css/tema.css">
    <?php require __DIR__ . '/includes/site/i18n-scripts.php'; ?>
</head>

<body class="login-page">
    <header class="login-header">
        <a class="login-brand" href="index.php" aria-label="EnsinoTec — início" data-i18n-aria-label="EnsinoTec — início">EnsinoTec</a>
        <a class="back-link" href="index.php"><span data-i18n="← Voltar ao início">← Voltar ao início</span></a>
    </header>
    <main class="login-main">
        <section class="login-card" aria-labelledby="login-title">
            <div class="user-emblem" aria-hidden="true"><svg viewBox="0 0 80 80">
                    <circle cx="40" cy="28" r="14" />
                    <path d="M13 70c0-17 10-26 27-26s27 9 27 26" />
                </svg></div>
            <h1 id="login-title"><span data-i18n="Bem-vindo de volta">Bem-vindo de volta</span></h1>
            <p class="login-subtitle"><span data-i18n="Entre para continuar aprendendo.">Entre para continuar aprendendo.</span></p>
            <?php if ($erro !== ''): ?>
                <p class="login-alert" role="alert"><span data-i18n-message><?= site_escape($erro) ?></span></p>
            <?php endif; ?>
            <form action="login.php" method="post">
                <?php if ($sucesso !== ''): ?><p class="login-alert" role="status"><span data-i18n-message><?= site_escape($sucesso) ?></span></p><?php endif; ?>
                <input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>">
                <div class="field">
                    <label for="email"><span data-i18n="E-mail">E-mail</span></label>
                    <div class="input-line">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="14" rx="2" />
                            <path d="m3 6 9 7 9-7" />
                        </svg>
                        <input type="email" id="email" name="email" placeholder="seuemail@exemplo.com" autocomplete="username" maxlength="150" value="<?= site_escape($email) ?>" required data-i18n-placeholder="seuemail@exemplo.com">
                    </div>
                </div>
                <div class="field">
                    <label for="senha"><span data-i18n="Senha">Senha</span></label>
                    <div class="input-line">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="5" y="10" width="14" height="11" rx="2" />
                            <path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3" />
                        </svg>
                        <input type="password" maxlength="72" id="senha" name="senha" placeholder="Digite sua senha" autocomplete="current-password" required data-i18n-placeholder="Digite sua senha">
                        <button class="password-toggle" type="button" aria-label="Mostrar senha" aria-controls="senha" aria-pressed="false" hidden data-i18n-aria-label="Mostrar senha"><svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg></button>
                    </div>
                </div>
                <div class="login-options">
                    <label class="remember-email"><input type="checkbox" id="manter-conectado" name="manter_conectado" value="1" <?= $manterConectado ? 'checked' : '' ?>> <span data-i18n="Manter conectado por 7 dias">Manter conectado por 7 dias</span></label>
                    <a class="password-help" href="recuperar-senha.php"><span data-i18n="Esqueceu a senha?">Esqueceu a senha?</span></a>
                </div>
                <button class="login-submit" type="submit"><span data-i18n="Entrar">Entrar</span> <span aria-hidden="true">→</span></button>
            </form>
            <p class="signup-link"><span data-i18n="Ainda não tem uma conta?">Ainda não tem uma conta?</span> <a href="cadastro.php"><span data-i18n="Cadastre-se">Cadastre-se</span></a></p>
        </section>
    </main>
    <footer class="login-footer"><span data-i18n="© 2026 EnsinoTec — TCC, Projeto Integrado">© 2026 EnsinoTec — TCC, Projeto Integrado</span></footer>
</body>

</html>