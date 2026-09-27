<?php
// ALTERADO: mostra os dados atuais da conta consultada no banco.
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
    'assets/css/configuracoes.css',
    'assets/css/excluir-conta.css',
];
require __DIR__ . '/includes/site/header.php';
?>
    <main class="pagina-configuracoes" id="conteudo-principal" tabindex="-1">
    <div class="main">

        
        <a href="index.php" class="back-button">
            <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m7-7-7 7 7 7"/></svg>
            Voltar
        </a>


        
        <header class="header">

            <div>
                <h1>Configurações</h1>
                <p>Gerencie suas preferências e informações da conta.</p>
            </div>

            <div class="user">

                <div class="notification">
                    <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0v5l-2 4h16l-2-4V8M10 21h4"/></svg>
                    <span>3</span>
                </div>

                <div class="user-info">

                    <div class="avatar">
                        <?= site_escape($iniciaisPerfil) ?>
                    </div>

                    <div>
                        <strong><?= site_escape($nomePerfil) ?></strong>
                        <small><?= site_escape($tipoPerfil) ?></small>
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
                        <h2>Perfil</h2>
                        <p>Informações pessoais da sua conta</p>
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
                            ID da conta: <?= (int) $perfilConta['id_usuario'] ?> · <?= site_escape($tipoPerfil) ?>
                        </p>

                    </div>

                    <button class="btn">
                        <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4 16 12-12 4 4L8 20H4zm9-9 4 4"/></svg>
                        Editar perfil
                    </button>

                </div>

            </div>


            
            <div class="card">

                <div class="card-title">

                    <div class="title-icon orange">
                        <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0v5l-2 4h16l-2-4V8M10 21h4"/></svg>
                    </div>

                    <div>
                        <h2>Notificações</h2>
                        <p>Escolha quais notificações deseja receber</p>
                    </div>

                </div>


                <div class="option">

                    <div>
                        <strong>Avisos da escola</strong>
                        <p>Receber comunicados importantes da escola</p>
                    </div>

                    <label class="switch">
                        <input type="checkbox" checked>
                        <span></span>
                    </label>

                </div>


                <div class="option">

                    <div>
                        <strong>Atividades</strong>
                        <p>Notificações sobre novas atividades</p>
                    </div>

                    <label class="switch">
                        <input type="checkbox" checked>
                        <span></span>
                    </label>

                </div>


                <div class="option">

                    <div>
                        <strong>Mensagens</strong>
                        <p>Receber mensagens de professores</p>
                    </div>

                    <label class="switch">
                        <input type="checkbox">
                        <span></span>
                    </label>

                </div>

            </div>


            
            <div class="card">

                <div class="card-title">

                    <div class="title-icon green">
                        <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 2 8 4v6c0 5-8 10-8 10S4 17 4 12V6zm0 0v20"/></svg>
                    </div>

                    <div>
                        <h2>Segurança</h2>
                        <p>Proteja sua conta escolar</p>
                    </div>

                </div>


                <div class="security-option">

                    <div>
                        <strong>Alterar senha</strong>
                        <p>Atualize sua senha de acesso</p>
                    </div>

                    <a class="arrow" href="recuperar-senha.php" aria-label="Recuperar ou alterar senha">→</a>

                </div>


                <div class="security-option">

                    <div>
                        <strong>Autenticação em duas etapas</strong>
                        <p><?= $autenticado ? (!empty($contaAtual["dois_fatores"]) ? "Ativada: código por e-mail." : "Desativada. Ative para pedir um código no login.") : "Entre na sua conta para configurar." ?></p>
                    </div>

                    <a class="arrow" href="seguranca.php" aria-label="Configurar autenticação em duas etapas">→</a>

                </div>

            </div>


            
            <div class="card">

                <div class="card-title">

                    <div class="title-icon purple">
                        <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3a9 9 0 1 0 0 18c4 0 1-5 4-5h2c6 0 3-13-6-13ZM7 9h.01M11 6h.01M16 8h.01M6 14h.01"/></svg>
                    </div>

                    <div>
                        <h2>Aparência</h2>
                        <p>Personalize a aparência do portal</p>
                    </div>

                </div>


                <div class="theme" role="group" aria-label="Aparência do site">

                    <button type="button" class="theme-option" data-theme-choice="light" aria-pressed="false">
                        <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0M12 2v2m0 16v2M2 12h2m16 0h2M5 5l2 2m10 10 2 2M5 19l2-2M17 7l2-2"/></svg>
                        <span>Claro</span>
                    </button>

                    <button type="button" class="theme-option" data-theme-choice="dark" aria-pressed="false">
                        <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 15A9 9 0 0 1 9 4a9 9 0 1 0 11 11Z"/></svg>
                        <span>Escuro</span>
                    </button>

                    <button type="button" class="theme-option" data-theme-choice="system" aria-pressed="false">
                        <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3h18v14H3zM12 17v4m-5 0h10"/></svg>
                        <span>Sistema</span>
                    </button>

                </div>

            </div>


            
            <div class="actions">

                <button type="button" class="cancel" data-theme-cancel>
                    Cancelar
                </button>

                <button type="button" class="save" data-theme-save>
                    <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4 12 5 5L20 6"/></svg>
                    Salvar alterações
                </button>

            </div>

        <p class="theme-status" id="theme-status" role="status" aria-live="polite"></p>
        <section class="card conta-exclusao" aria-labelledby="excluir-titulo">
            <h2 id="excluir-titulo">Excluir conta</h2>
            <?php if ($erroExclusao !== ''): ?>
            <p role="alert"><?= site_escape($erroExclusao) ?></p>
            <?php endif; ?>
            <?php if ($autenticado): ?>
            <p>Ao excluir sua conta, você perderá o acesso e o progresso de aprendizagem vinculado a ela. Essa ação não pode ser desfeita.</p>
            <button class="excluir-botao" type="button" id="abrir-exclusao" aria-haspopup="dialog" aria-controls="confirmar-exclusao" hidden>Excluir minha conta</button>
            <noscript><p>Ative o JavaScript para abrir a confirmação de exclusão.</p></noscript>
            <dialog id="confirmar-exclusao" class="excluir-dialog" aria-labelledby="confirmar-titulo" aria-describedby="confirmar-descricao">
                <h2 id="confirmar-titulo">Deseja mesmo excluir sua conta?</h2>
                <p id="confirmar-descricao">A conta <strong><?= site_escape((string) $usuario['email']) ?></strong> será excluída permanentemente. Você será desconectado do EnsinoTec.</p>
                <form action="configuracoes.php" method="post" id="form-exclusao">
                    <input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>">
                    <input type="hidden" name="acao" value="excluir_conta">
                    <div class="excluir-acoes">
                        <button type="button" class="excluir-cancelar" id="cancelar-exclusao" autofocus>Não, cancelar</button>
                        <button type="submit" class="excluir-botao" name="confirmar_exclusao" value="sim">Sim, excluir minha conta</button>
                    </div>
                </form>
            </dialog>
            <?php else: ?>
            <p><a href="login.php">Entre na sua conta</a> para solicitar a exclusão.</p>
            <?php endif; ?>
        </section>
        </section>

    </div>
</main>
<script src="assets/js/excluir-conta.js" defer></script>
<?php require __DIR__ . '/includes/site/footer.php'; ?>
