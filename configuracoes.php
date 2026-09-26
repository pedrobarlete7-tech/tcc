<?php
// ALTERADO: controles de aparência com prévia, salvar e cancelar.
$tituloPagina = 'Configurações — EnsinoTec';
$estilosPagina = [
    'assets/css/configuracoes.css',
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
                        JS
                    </div>

                    <div>
                        <strong>João Silva</strong>
                        <small>Aluno</small>
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
                        JS
                    </div>

                    <div class="profile-info">

                        <h3>João Silva</h3>

                        <p>
                            <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5h18v14H3zM3 5l9 8 9-8"/></svg>
                            joao@email.com
                        </p>

                        <p>
                            <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5h18v14H3zM14 9h4m-4 4h4M6 9h4v6H6z"/></svg>
                            Matrícula: 20260125
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

                    <button class="arrow">
                        <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
                    </button>

                </div>


                <div class="security-option">

                    <div>
                        <strong>Autenticação em duas etapas</strong>
                        <p>Adicione uma camada extra de segurança</p>
                    </div>

                    <button class="arrow">
                        <svg class="settings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
                    </button>

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
        </section>

    </div>
</main>
<?php require __DIR__ . '/includes/site/footer.php'; ?>