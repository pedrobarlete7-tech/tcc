<?php
// ALTERADO: remove o bloco de notificações das configurações.


require_once __DIR__ . '/includes/site/auth.php';
header('Cache-Control: no-store');
if (!$autenticado) {
    header('Location: login.php', true, 303);
    exit;
}
$perfilConta = $contaAtual;
$nomePerfil = (string) $perfilConta['nome'];
$emailPerfil = (string) $perfilConta['email'];
$tipoPerfil = ['aluno' => 'Aluno', 'professor' => 'Professor', 'administrador' => 'Administrador'][$perfilConta['tipo_usuario']] ?? 'Usuário';
$partesNome = preg_split('/\s+/u', trim($nomePerfil), -1, PREG_SPLIT_NO_EMPTY);
$iniciaisPerfil = $partesNome ? mb_substr($partesNome[0], 0, 1, 'UTF-8') : '?';
if (count($partesNome) > 1) $iniciaisPerfil .= mb_substr($partesNome[count($partesNome) - 1], 0, 1, 'UTF-8');
$iniciaisPerfil = mb_strtoupper($iniciaisPerfil, 'UTF-8');
$nomeUsuario = $nomePerfil;
$erroExclusao = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$autenticado) {
        header('Location: login.php', true, 303);
        exit;
    }
    if (!auth_csrf_valido() || ($_POST['acao'] ?? '') !== 'excluir_conta' || ($_POST['confirmar_exclusao'] ?? '') !== 'sim') {
        http_response_code(403);
        $erroExclusao = 'Não foi possível confirmar a solicitação. Abra a confirmação e tente novamente.';
    } else {
        require __DIR__ . '/actions/conexao.php';
        try {
            $pdo->beginTransaction();
            $excluir = $pdo->prepare('DELETE FROM usuario WHERE id_usuario = ?');
            $excluir->execute([(int) $_SESSION['usuario']['id_usuario']]);
            $pdo->commit();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('[EnsinoTec] Falha ao excluir conta. Código: ' . $e->getCode());
            http_response_code(503);
            $erroExclusao = 'Não foi possível excluir sua conta agora. Tente novamente mais tarde.';
        }
        if ($erroExclusao === '') {
            require __DIR__ . '/sair.php';
            exit;
        }
    }
}
$tituloPagina = 'Configurações — EnsinoTec';
$estilosPagina = [
    'assets/css/configuracoes.css?v=progresso2',
    'assets/css/excluir-conta.css',
];
require __DIR__ . '/includes/site/header.php';
?>
    <main class="pagina-configuracoes" id="conteudo-principal" tabindex="-1">
    <div class="main">

        
        <a href="index.php" class="back-button">
            <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m7-7-7 7 7 7"/></svg>
            <span data-i18n="Voltar">Voltar</span>
        </a>


        
        <header class="header">

            <div>
                <h1><span data-i18n="Configurações">Configurações</span></h1>
                <p><span data-i18n="Gerencie suas preferências e informações da conta.">Gerencie suas preferências e informações da conta.</span></p>
            </div>

            <div class="user">

                <div class="user-info">

                    <div class="avatar">
                        <?= site_escape($iniciaisPerfil) ?>
                    </div>

                    <div>
                        <strong><?= site_escape($nomePerfil) ?></strong>
                        <small><span data-i18n-message><?= site_escape($tipoPerfil) ?></span></small>
                    </div>

                </div>

            </div>

        </header>


        
        <section class="settings">

            
            <div class="card">

                <div class="card-title">

                    <div class="title-icon blue">
                        <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0M4 21v-3a8 8 0 0 1 16 0v3"/></svg>
                    </div>

                    <div>
                        <h2><span data-i18n="Perfil">Perfil</span></h2>
                        <p><span data-i18n="Informações pessoais da sua conta">Informações pessoais da sua conta</span></p>
                    </div>

                </div>


                <div class="profile">

                    <div class="big-avatar">
                        <?= site_escape($iniciaisPerfil) ?>
                    </div>

                    <div class="profile-info">

                        <h3><?= site_escape($nomePerfil) ?></h3>

                        <p>
                            <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5h18v14H3zM3 5l9 8 9-8"/></svg>
                            <?= site_escape($emailPerfil) ?>
                        </p>

                        <p>
                            <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5h18v14H3zM14 9h4m-4 4h4M6 9h4v6H6z"/></svg>
                            <span data-i18n="ID da conta:">ID da conta:</span> <?= (int) $perfilConta['id_usuario'] ?> · <span data-i18n-message><?= site_escape($tipoPerfil) ?></span>
                        </p>

                    </div>

                    <button class="btn">
                        <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4 16 12-12 4 4L8 20H4zm9-9 4 4"/></svg>
                        <span data-i18n="Editar perfil">Editar perfil</span>
                    </button>

                </div>

            </div>


            
            <div class="card">

                <div class="card-title">

                    <div class="title-icon green">
                        <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 2 8 4v6c0 5-8 10-8 10S4 17 4 12V6zm0 0v20"/></svg>
                    </div>

                    <div>
                        <h2><span data-i18n="Segurança">Segurança</span></h2>
                        <p><span data-i18n="Proteja sua conta escolar">Proteja sua conta escolar</span></p>
                    </div>

                </div>


                <div class="security-option">

                    <div>
                        <strong><span data-i18n="Alterar senha">Alterar senha</span></strong>
                        <p><span data-i18n="Atualize sua senha de acesso">Atualize sua senha de acesso</span></p>
                    </div>

                    <a class="arrow" href="recuperar-senha.php" aria-label="Recuperar ou alterar senha" data-i18n-aria-label="Recuperar ou alterar senha">→</a>

                </div>


                <div class="security-option">

                    <div>
                        <strong><span data-i18n="Autenticação em duas etapas">Autenticação em duas etapas</span></strong>
                        <p data-i18n-message><?= $autenticado ? (!empty($contaAtual["dois_fatores"]) ? "Ativada: código por e-mail." : "Desativada. Ative para pedir um código no login.") : "Entre na sua conta para configurar." ?></p>
                    </div>

                    <a class="arrow" href="seguranca.php" aria-label="Configurar autenticação em duas etapas" data-i18n-aria-label="Configurar autenticação em duas etapas">→</a>

                </div>

            </div>


            
            <div class="card">

                <div class="card-title">

                    <div class="title-icon purple">
                        <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3a9 9 0 1 0 0 18c4 0 1-5 4-5h2c6 0 3-13-6-13ZM7 9h.01M11 6h.01M16 8h.01M6 14h.01"/></svg>
                    </div>

                    <div>
                        <h2><span data-i18n="Aparência">Aparência</span></h2>
                        <p><span data-i18n="Personalize a aparência do portal">Personalize a aparência do portal</span></p>
                    </div>

                </div>


                <div class="theme" role="group" aria-label="Aparência do site" data-i18n-aria-label="Aparência do site">

                    <button type="button" class="theme-option" data-theme-choice="light" aria-pressed="false">
                        <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0M12 2v2m0 16v2M2 12h2m16 0h2M5 5l2 2m10 10 2 2M5 19l2-2M17 7l2-2"/></svg>
                        <span><span data-i18n="Claro">Claro</span></span>
                    </button>

                    <button type="button" class="theme-option" data-theme-choice="dark" aria-pressed="false">
                        <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 15A9 9 0 0 1 9 4a9 9 0 1 0 11 11Z"/></svg>
                        <span><span data-i18n="Escuro">Escuro</span></span>
                    </button>

                    <button type="button" class="theme-option" data-theme-choice="system" aria-pressed="false">
                        <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3h18v14H3zM12 17v4m-5 0h10"/></svg>
                        <span><span data-i18n="Sistema">Sistema</span></span>
                    </button>

                </div>

            </div>


            
            <?php require __DIR__ . '/includes/site/idioma-card.php'; ?>

            <div class="actions">

                <button type="button" class="cancel" data-theme-cancel>
                    <span data-i18n="Cancelar">Cancelar</span>
                </button>

                <button type="button" class="save" data-theme-save>
                    <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4 12 5 5L20 6"/></svg>
                    <span data-i18n="Salvar alterações">Salvar alterações</span>
                </button>

            </div>

        <p class="theme-status" id="theme-status" data-i18n-message role="status" aria-live="polite"></p>
        <section class="card conta-exclusao" aria-labelledby="excluir-titulo">
            <h2 id="excluir-titulo"><span data-i18n="Excluir conta">Excluir conta</span></h2>
            <?php if ($erroExclusao !== ''): ?>
            <p role="alert"><span data-i18n-message><?= site_escape($erroExclusao) ?></span></p>
            <?php endif; ?>
            <?php if ($autenticado): ?>
            <p><span data-i18n="Ao excluir sua conta, você perderá o acesso e os dados vinculados a ela. Essa ação não pode ser desfeita.">Ao excluir sua conta, você perderá o acesso e os dados vinculados a ela. Essa ação não pode ser desfeita.</span></p>
            <button class="excluir-botao" type="button" id="abrir-exclusao" aria-haspopup="dialog" aria-controls="confirmar-exclusao" hidden><span data-i18n="Excluir minha conta">Excluir minha conta</span></button>
            <noscript><p><span data-i18n="Ative o JavaScript para abrir a confirmação de exclusão.">Ative o JavaScript para abrir a confirmação de exclusão.</span></p></noscript>
            <dialog id="confirmar-exclusao" class="excluir-dialog" aria-labelledby="confirmar-titulo" aria-describedby="confirmar-descricao">
                <h2 id="confirmar-titulo"><span data-i18n="Deseja mesmo excluir sua conta?">Deseja mesmo excluir sua conta?</span></h2>
                <p id="confirmar-descricao"><span data-i18n="A conta">A conta</span> <strong><?= site_escape((string) $usuario['email']) ?></strong> <span data-i18n="será excluída permanentemente. Você será desconectado do EnsinoTec.">será excluída permanentemente. Você será desconectado do EnsinoTec.</span></p>
                <form action="configuracoes.php" method="post" id="form-exclusao">
                    <input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>">
                    <input type="hidden" name="acao" value="excluir_conta">
                    <div class="excluir-acoes">
                        <button type="button" class="excluir-cancelar" id="cancelar-exclusao" autofocus><span data-i18n="Não, cancelar">Não, cancelar</span></button>
                        <button type="submit" class="excluir-botao" name="confirmar_exclusao" value="sim"><span data-i18n="Sim, excluir minha conta">Sim, excluir minha conta</span></button>
                    </div>
                </form>
            </dialog>
            <?php else: ?>
            <p><a href="login.php"><span data-i18n="Entre na sua conta">Entre na sua conta</span></a> <span data-i18n="para solicitar a exclusão.">para solicitar a exclusão.</span></p>
            <?php endif; ?>
        </section>
        </section>

    </div>
</main>
<script src="assets/js/excluir-conta.js" defer></script>
<?php require __DIR__ . '/includes/site/footer.php'; ?>
