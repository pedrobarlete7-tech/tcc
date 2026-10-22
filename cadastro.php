<?php
// ALTERADO: integra idioma e progresso preservando as funções atuais.


require_once __DIR__ . '/includes/site/senha.php';
require_once __DIR__ . '/includes/site/auth.php';
header('Cache-Control: no-store');
if ($autenticado) { header('Location: index.php', true, 303); exit; }
$nome = '';
$email = '';
$aceite = false;
$erros = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = is_string($_POST['nome'] ?? null) ? trim($_POST['nome']) : '';
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $senha = is_string($_POST['senha'] ?? null) ? $_POST['senha'] : '';
    $confirmacao = is_string($_POST['confirmar_senha'] ?? null) ? $_POST['confirmar_senha'] : '';
    $aceite = ($_POST['aceite_termos'] ?? '') === '1';
    $email = strtolower($email);
    if (!auth_csrf_valido()) $erros[] = 'Solicitação expirada. Tente novamente.';
    if ($nome === '' || mb_strlen($nome) > 100) $erros[] = 'Informe seu nome com até 100 caracteres.';
    if (!email_valido($email)) $erros[] = 'Informe um e-mail válido com até 150 caracteres e até 64 antes do @.';
    if (($erroSenha = senha_erro($senha)) !== '') $erros[] = $erroSenha;
    if ($senha !== $confirmacao) $erros[] = 'As senhas não coincidem.';
    if (!$aceite) $erros[] = 'Aceite os termos de uso para continuar.';
    if (!$erros) {
        require __DIR__ . '/actions/conexao.php';
        try {
            auth_cadastrar($pdo, $nome, $email, $senha);
            $_SESSION['cadastro_sucesso'] = 'Conta criada com sucesso! Entre com seu e-mail e senha.';
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            header('Location: login.php', true, 303);
            exit;
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) === 1062) {
                $erros[] = 'Este e-mail já está cadastrado. Faça login com sua conta.';
            } else {
                error_log('[EnsinoTec] Falha no cadastro. Código: ' . $e->getCode());
                http_response_code(503);
                $erros[] = 'Não foi possível criar a conta agora. Tente novamente mais tarde.';
            }
        } catch (RuntimeException $e) {
            $erros[] = 'Não foi possível criar a conta agora. Confira os dados e tente novamente.';
            error_log('[EnsinoTec] Cadastro: ' . get_class($e) . ' em ' . $e->getFile() . ':' . $e->getLine());
        } catch (Throwable $e) {
            http_response_code(503);
            $erros[] = 'Não foi possível criar a conta agora. Tente novamente mais tarde.';
            error_log('[EnsinoTec] Cadastro: ' . get_class($e) . ' em ' . $e->getFile() . ':' . $e->getLine());
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
    <meta name="theme-color" content="#100c0e">
    <title data-i18n-message>Crie sua conta — EnsinoTec</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/cadastro.css">
    <script src="assets/js/cadastro.js" defer></script>
    <link rel="stylesheet" href="assets/css/tema.css">
<?php require __DIR__ . '/includes/site/i18n-scripts.php'; ?>
</head>
<body class="login-page register-page">
    <header class="login-header">
        <a class="login-brand" href="index.php" aria-label="EnsinoTec — início" data-i18n-aria-label="EnsinoTec — início">EnsinoTec</a>
        <a class="back-link" href="index.php"><span data-i18n="← Voltar ao início">← Voltar ao início</span></a>
    </header>
    <main class="login-main">
        <section class="login-card register-card" aria-labelledby="register-title">
            <h1 id="register-title"><span data-i18n="Crie sua conta">Crie sua conta</span></h1>
            <p class="login-subtitle"><span data-i18n="Junte-se ao EnsinoTec e comece sua">Junte-se ao EnsinoTec e comece sua</span><br class="desktop-break"> <span data-i18n="jornada de aprendizagem.">jornada de aprendizagem.</span></p>
            <?php if ($erros): ?>
            <div class="login-alert" role="alert"><ul>
                <?php foreach ($erros as $erro): ?>
                <li><span data-i18n-message><?= site_escape($erro) ?></span></li>
                <?php endforeach; ?>
            </ul></div>
            <?php endif; ?>
            <form action="cadastro.php" method="post">
                <input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>">
                <div class="field">
                    <label for="nome"><span data-i18n="Nome completo">Nome completo</span></label>
                    <input id="nome" name="nome" type="text" placeholder="Ex.: Maria Silva" autocomplete="name" maxlength="100" value="<?= site_escape($nome) ?>" required data-i18n-placeholder="Ex.: Maria Silva">
                </div>
                <div class="field">
                    <label for="email"><span data-i18n="E-mail">E-mail</span></label>
                    <input id="email" name="email" type="email" placeholder="seuemail@exemplo.com" autocomplete="email" maxlength="150" value="<?= site_escape($email) ?>" required data-i18n-placeholder="seuemail@exemplo.com">
                </div>
                <div class="password-grid">
                    <div class="field">
                        <label for="senha"><span data-i18n="Senha">Senha</span></label>
                        <input id="senha" name="senha" type="password" maxlength="72" placeholder="Mínimo 8 caracteres" autocomplete="new-password" minlength="8" aria-describedby="password-hint" required data-i18n-placeholder="Mínimo 8 caracteres">
                    </div>
                    <div class="field">
                        <label for="confirmar_senha"><span data-i18n="Confirmar senha">Confirmar senha</span></label>
                        <input id="confirmar_senha" name="confirmar_senha" type="password" maxlength="72" placeholder="Repita sua senha" autocomplete="new-password" minlength="8" aria-describedby="password-error" required data-i18n-placeholder="Repita sua senha">
                    </div>
                </div>
                <div class="password-options">
                    <p id="password-hint"><span data-i18n="Use pelo menos 8 caracteres. Você pode usar uma frase como senha.">Use pelo menos 8 caracteres. Você pode usar uma frase como senha.</span></p>
                    <button type="button" class="show-passwords" aria-pressed="false" aria-controls="senha confirmar_senha" hidden><span data-i18n="Mostrar senhas">Mostrar senhas</span></button>
                </div>
                <p id="password-error" class="field-error" aria-live="polite"></p>
                <div class="terms-choice">
                    <input type="checkbox" id="aceite_termos" name="aceite_termos" value="1" <?= $aceite ? 'checked' : '' ?> required>
                    <label for="aceite_termos"><span data-i18n="Li e aceito os termos de uso do EnsinoTec.">Li e aceito os termos de uso do EnsinoTec.</span></label>
                </div>
                <details class="terms-details">
                    <summary><span data-i18n="Ler termos de uso">Ler termos de uso</span></summary>
                    <p><span data-i18n="Termos de uso disponíveis em português.">Termos de uso disponíveis em português.</span></p><div class="terms-content" lang="pt-BR"><?php require __DIR__ . '/includes/site/termos.php'; ?></div>
                </details>
                <button class="login-submit" type="submit"><span data-i18n="Criar conta">Criar conta</span> <span aria-hidden="true">→</span></button>
            </form>
            <p class="signup-link"><span data-i18n="Já possui uma conta?">Já possui uma conta?</span> <a href="login.php"><span data-i18n="Fazer login">Fazer login</span></a></p>
        </section>
    </main>
    <footer class="login-footer"><span data-i18n="© 2026 EnsinoTec — Todos os direitos reservados">© 2026 EnsinoTec — Todos os direitos reservados</span></footer>
</body>
</html>
