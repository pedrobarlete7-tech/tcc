<?php
// ALTERADO: permite entrar com a nova senha sem repetir a confirmação já realizada.
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../../actions/conexao.php';
header('Cache-Control: no-store');
header('Referrer-Policy: no-referrer');
$erro = '';
$aviso = '';
$email = '';
if ($fluxo === 'configurar' && !$autenticado) {
    header('Location: login.php', true, 303);
    exit;
}
if ($fluxo === 'login' && !isset($_SESSION['desafio_login'])) {
    header('Location: login.php', true, 303);
    exit;
}
if ($fluxo === 'recuperar' && isset($_SESSION['recuperacao']['limite']) && $_SESSION['recuperacao']['limite'] < time()) unset($_SESSION['recuperacao']);
try {
    $contaConfig = $fluxo === 'configurar' ? seguranca_conta($pdo, (int) $usuario['id_usuario']) : null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!auth_csrf_valido()) throw new RuntimeException('Solicitação expirada. Atualize a página e tente novamente.');
        $acao = is_string($_POST['acao'] ?? null) ? $_POST['acao'] : '';
        $codigo = is_string($_POST['codigo'] ?? null) ? trim($_POST['codigo']) : '';
        if ($acao === 'reiniciar') {
            unset($_SESSION['recuperacao'], $_SESSION['configurar_fator']);
            if ($fluxo === 'login') {
                unset($_SESSION['desafio_login']);
                header('Location: login.php', true, 303);
                exit;
            }
        } elseif ($fluxo === 'login' && $acao === 'conferir') {
            $conta = seguranca_conferir($pdo, $_SESSION['desafio_login'], $codigo, 'login');
            if (!$conta) throw new RuntimeException('Código inválido, expirado ou sem tentativas restantes. Confira o último código ou volte para entrar novamente.');
            auth_concluir($conta);
            header('Location: index.php', true, 303);
            exit;
        } elseif ($fluxo === 'recuperar' && $acao === 'solicitar') {
            $email = is_string($_POST['email'] ?? null) ? strtolower(trim($_POST['email'])) : '';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) throw new RuntimeException('Informe um e-mail válido.');
            if (($_SESSION['recuperacao_ultima'] ?? 0) > time() - 60) throw new RuntimeException('Aguarde um minuto antes de solicitar outro código.');
            $_SESSION['recuperacao_ultima'] = time();
            $q = $pdo->prepare('SELECT id_usuario FROM usuario WHERE email = ? AND ativo = 1');
            $q->execute([$email]);
            $idUsuario = (int) $q->fetchColumn();
            $id = bin2hex(random_bytes(32));
            if ($idUsuario) {
                try {
                    $id = seguranca_emitir($pdo, seguranca_conta($pdo, $idUsuario), 'recuperar');
                } catch (RuntimeException $e) {
                    error_log('[EnsinoTec] Não foi emitido código de recuperação: ' . $e->getMessage());
                }
            }
            $_SESSION['recuperacao'] = ['etapa' => 'codigo', 'id' => $id, 'finalidade' => 'recuperar', 'limite' => time() + 600];
        } elseif ($fluxo === 'recuperar' && $acao === 'conferir') {
            $estado = $_SESSION['recuperacao'] ?? [];
            if (($estado['etapa'] ?? '') !== 'codigo') throw new RuntimeException('Comece uma nova recuperação.');
            $conta = seguranca_conferir($pdo, $estado['id'], $codigo, $estado['finalidade']);
            if (!$conta) throw new RuntimeException('Código inválido, expirado ou sem tentativas restantes. Solicite um novo código para tentar novamente.');
            if ($conta['dois_fatores'] && $estado['finalidade'] === 'recuperar') {
                unset($_SESSION['recuperacao']);
                $id = seguranca_emitir($pdo, $conta, 'recuperar_2fa');
                $_SESSION['recuperacao'] = ['etapa' => 'codigo', 'id' => $id, 'finalidade' => 'recuperar_2fa', 'limite' => time() + 600];
            } else {
                $_SESSION['recuperacao'] = ['etapa' => 'senha', 'usuario' => (int) $conta['id_usuario'], 'versao' => (int) $conta['versao'], 'limite' => time() + 300];
                session_regenerate_id(true);
            }
        } elseif ($fluxo === 'recuperar' && $acao === 'salvar') {
            $estado = $_SESSION['recuperacao'] ?? [];
            if (($estado['etapa'] ?? '') !== 'senha' || $estado['limite'] < time()) throw new RuntimeException('A confirmação expirou. Comece novamente.');
            $senha = is_string($_POST['senha'] ?? null) ? $_POST['senha'] : '';
            $confirmacao = is_string($_POST['confirmar_senha'] ?? null) ? $_POST['confirmar_senha'] : '';
            if (mb_strlen($senha) < 6 || strlen($senha) > 72 || str_contains($senha, "\0")) throw new RuntimeException('Use uma senha de pelo menos 6 caracteres. Se for muito longa, escolha uma menor.');
            if ($senha !== $confirmacao) throw new RuntimeException('As senhas não coincidem.');
            seguranca_alterar($pdo, $estado['usuario'], $estado['versao'], $senha, null);
            $_SESSION = [];
            session_regenerate_id(true);
            $_SESSION['login_recuperado'] = [
                'usuario' => $estado['usuario'],
                'versao' => $estado['versao'] + 1,
                'limite' => time() + 300,
            ];
            $_SESSION['cadastro_sucesso'] = 'Senha atualizada! Entre com a nova senha.';
            header('Location: login.php', true, 303);
            exit;
        } elseif ($fluxo === 'configurar' && $acao === 'solicitar') {
            if (($_SESSION['fator_bloqueado'] ?? 0) > time()) throw new RuntimeException('Aguarde cinco minutos antes de tentar novamente.');
            $senha = is_string($_POST['senha_atual'] ?? null) ? $_POST['senha_atual'] : '';
            if (strlen($senha) > 72 || !password_verify($senha, $contaConfig['senha'])) {
                $_SESSION['fator_falhas'] = ($_SESSION['fator_falhas'] ?? 0) + 1;
                if ($_SESSION['fator_falhas'] >= 5) {
                    $_SESSION['fator_bloqueado'] = time() + 300;
                    $_SESSION['fator_falhas'] = 0;
                }
                throw new RuntimeException('Senha atual inválida.');
            }
            $finalidade = $contaConfig['dois_fatores'] ? 'desativar' : 'ativar';
            $id = seguranca_emitir($pdo, $contaConfig, $finalidade);
            $_SESSION['configurar_fator'] = ['id' => $id, 'finalidade' => $finalidade];
        } elseif ($fluxo === 'configurar' && $acao === 'conferir') {
            $estado = $_SESSION['configurar_fator'] ?? [];
            if (!$estado) throw new RuntimeException('Confirme sua senha primeiro.');
            $conta = seguranca_conferir($pdo, $estado['id'], $codigo, $estado['finalidade']);
            if (!$conta || (int) $conta['id_usuario'] !== (int) $usuario['id_usuario']) throw new RuntimeException('Código inválido, expirado ou sem tentativas restantes.');
            seguranca_alterar($pdo, (int) $conta['id_usuario'], (int) $conta['versao'], null, $estado['finalidade'] === 'ativar' ? 1 : 0);
            unset($_SESSION['configurar_fator']);
            $contaConfig = seguranca_conta($pdo, (int) $conta['id_usuario']);
            auth_concluir($contaConfig);
            $aviso = $contaConfig['dois_fatores'] ? 'Verificação em duas etapas ativada.' : 'Verificação em duas etapas desativada.';
        } else {
            throw new RuntimeException('Solicitação inválida.');
        }
    }
} catch (PDOException $e) {
    http_response_code(503);
    error_log('[EnsinoTec] Falha na segurança. Código: ' . $e->getCode());
    $erro = 'Não foi possível continuar. Confira a conexão e se o arquivo seguranca.sql foi importado.';
} catch (RuntimeException $e) {
    $erro = $e->getMessage();
}
$estado = $fluxo === 'recuperar' ? ($_SESSION['recuperacao'] ?? []) : ($_SESSION['configurar_fator'] ?? []);
$etapa = $fluxo === 'login' ? 'codigo' : ($fluxo === 'configurar' ? ($estado ? 'codigo' : 'configurar') : ($estado['etapa'] ?? 'email'));
$titulo = $fluxo === 'recuperar' ? 'Recuperar senha' : ($fluxo === 'login' ? 'Confirme seu acesso' : 'Verificação em duas etapas');
$acaoPagina = ['recuperar' => 'recuperar-senha.php', 'login' => 'verificar-codigo.php', 'configurar' => 'seguranca.php'][$fluxo];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="assets/js/tema.js"></script>
    <title><?= site_escape($titulo) ?> — EnsinoTec</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/cadastro.css">
    <link rel="stylesheet" href="assets/css/seguranca.css">
    <link rel="stylesheet" href="assets/css/tema.css">
</head>

<body class="login-page register-page">
    <header class="login-header"><a class="login-brand" href="index.php">EnsinoTec</a><a class="back-link" href="<?= $fluxo === 'configurar' ? 'configuracoes.php' : 'login.php' ?>">← Voltar</a></header>
    <main class="login-main">
        <section class="login-card register-card" aria-labelledby="titulo-seguranca">
            <h1 id="titulo-seguranca"><?= site_escape($titulo) ?></h1>
            <?php if ($erro): ?><p class="login-alert" role="alert"><?= site_escape($erro) ?></p><?php endif; ?>
            <?php if ($aviso): ?><p class="login-alert" role="status"><?= site_escape($aviso) ?></p><?php endif; ?>
            <form action="<?= $acaoPagina ?>" method="post">
                <input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>">
                <?php if ($etapa === 'email'): ?>
                    <p class="login-subtitle">Informe o e-mail da sua conta para receber um código de recuperação.</p>
                    <input type="hidden" name="acao" value="solicitar">
                    <div class="field"><label for="email">E-mail cadastrado</label><input id="email" name="email" type="email" autocomplete="email" maxlength="150" value="<?= site_escape($email) ?>" required></div>
                    <button class="login-submit" type="submit">Solicitar código</button>
                <?php elseif ($etapa === 'configurar'): ?>
                    <p class="login-subtitle">Status: <strong><?= !empty($contaConfig['dois_fatores']) ? 'Ativada' : 'Desativada' ?></strong>. Confirme sua senha e depois o código enviado ao e-mail da conta.</p>
                    <input type="hidden" name="acao" value="solicitar">
                    <div class="field"><label for="senha_atual">Senha atual</label><input id="senha_atual" name="senha_atual" type="password" autocomplete="current-password" required></div>
                    <button class="login-submit" type="submit"><?= !empty($contaConfig['dois_fatores']) ? 'Desativar duas etapas' : 'Ativar duas etapas' ?></button>
                <?php elseif ($etapa === 'codigo'): ?>
                    <p class="login-subtitle"><?php if ($fluxo === 'recuperar' && ($estado['finalidade'] ?? '') === 'recuperar'): ?>Se houver uma conta ativa com esse e-mail, um código será gerado. Aguarde um minuto se já fez uma solicitação recente.<?php elseif (($estado['finalidade'] ?? '') === 'recuperar_2fa'): ?>Sua conta usa duas etapas. Confira o novo código de confirmação adicional enviado ao e-mail.<?php else: ?>Confira o código enviado ao e-mail da conta para continuar.<?php endif; ?> O código vale por 10 minutos e permite até 5 tentativas.</p>
                    <input type="hidden" name="acao" value="conferir">
                    <div class="field"><label for="codigo">Código de 8 números</label><input id="codigo" name="codigo" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{8}" minlength="8" maxlength="8" required></div>
                    <button class="login-submit" type="submit">Confirmar código</button>
                <?php else: ?>
                    <p class="login-subtitle">Crie sua nova senha. As sessões anteriores serão encerradas.</p>
                    <input type="hidden" name="acao" value="salvar">
                    <div class="field"><label for="senha">Nova senha</label><input id="senha" name="senha" type="password" autocomplete="new-password" minlength="6" required></div>
                    <div class="field"><label for="confirmar_senha">Confirmar nova senha</label><input id="confirmar_senha" name="confirmar_senha" type="password" autocomplete="new-password" minlength="6" required></div>
                    <button class="login-submit" type="submit">Salvar nova senha</button>
                <?php endif; ?>
            </form>
            <?php if ($etapa === 'codigo' || $etapa === 'senha'): ?>
                <form action="<?= $acaoPagina ?>" method="post" class="seguranca-reiniciar"><input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>"><button type="submit" name="acao" value="reiniciar">Cancelar e começar novamente</button></form>
            <?php endif; ?>
            <p class="seguranca-teste">Teste no XAMPP: nenhum e-mail real é enviado. Consulte o arquivo gerado na pasta local de e-mails indicada nas instruções.</p>
        </section>
    </main>
    <footer class="login-footer">© 2026 EnsinoTec — TCC, Projeto Integrado</footer>
</body>

</html>