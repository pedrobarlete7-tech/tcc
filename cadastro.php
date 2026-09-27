<?php
// ALTERADO: salva contas de aluno com senha protegida na tabela usuario.
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
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) $erros[] = 'Informe um e-mail válido com até 150 caracteres.';
    if (mb_strlen($senha) < 6 || strlen($senha) > 72 || str_contains($senha, "\0")) $erros[] = 'A senha deve ter pelo menos 6 caracteres. Se for muito longa, use uma senha menor.';
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
    <title>Crie sua conta — EnsinoTec</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/cadastro.css">
    <script src="assets/js/cadastro.js" defer></script>
    <link rel="stylesheet" href="assets/css/tema.css">
</head>
<body class="login-page register-page">
    <header class="login-header">
        <a class="login-brand" href="index.php" aria-label="EnsinoTec — início">EnsinoTec</a>
        <a class="back-link" href="index.php">← Voltar ao início</a>
    </header>
    <main class="login-main">
        <section class="login-card register-card" aria-labelledby="register-title">
            <h1 id="register-title">Crie sua conta</h1>
            <p class="login-subtitle">Junte-se ao EnsinoTec e comece sua<br class="desktop-break"> jornada de aprendizagem.</p>
            <?php if ($erros): ?>
            <div class="login-alert" role="alert"><ul>
                <?php foreach ($erros as $erro): ?>
                <li><?= site_escape($erro) ?></li>
                <?php endforeach; ?>
            </ul></div>
            <?php endif; ?>
            <form action="cadastro.php" method="post">
                <input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>">
                <div class="field">
                    <label for="nome">Nome completo</label>
                    <input id="nome" name="nome" type="text" placeholder="Ex.: Maria Silva" autocomplete="name" maxlength="100" value="<?= site_escape($nome) ?>" required>
                </div>
                <div class="field">
                    <label for="email">E-mail</label>
                    <input id="email" name="email" type="email" placeholder="seuemail@exemplo.com" autocomplete="email" maxlength="150" value="<?= site_escape($email) ?>" required>
                </div>
                <div class="password-grid">
                    <div class="field">
                        <label for="senha">Senha</label>
                        <input id="senha" name="senha" type="password" placeholder="Mínimo 6 caracteres" autocomplete="new-password" minlength="6" aria-describedby="password-hint" required>
                    </div>
                    <div class="field">
                        <label for="confirmar_senha">Confirmar senha</label>
                        <input id="confirmar_senha" name="confirmar_senha" type="password" placeholder="Repita sua senha" autocomplete="new-password" minlength="6" aria-describedby="password-error" required>
                    </div>
                </div>
                <div class="password-options">
                    <p id="password-hint">Use pelo menos 6 caracteres.</p>
                    <button type="button" class="show-passwords" aria-pressed="false" aria-controls="senha confirmar_senha" hidden>Mostrar senhas</button>
                </div>
                <p id="password-error" class="field-error" aria-live="polite"></p>
                <div class="terms-choice">
                    <input type="checkbox" id="aceite_termos" name="aceite_termos" value="1" <?= $aceite ? 'checked' : '' ?> required>
                    <label for="aceite_termos">Li e aceito os termos de uso do EnsinoTec.</label>
                </div>
                <details class="terms-details">
                    <summary>Ler termos de uso</summary>
                    <div class="terms-content"><?php require __DIR__ . '/includes/site/termos.php'; ?></div>
                </details>
                <button class="login-submit" type="submit">Criar conta <span aria-hidden="true">→</span></button>
            </form>
            <p class="signup-link">Já possui uma conta? <a href="login.php">Fazer login</a></p>
        </section>
    </main>
    <footer class="login-footer">© 2026 EnsinoTec — Todos os direitos reservados</footer>
</body>
</html>
