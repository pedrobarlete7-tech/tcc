<header class="site-header">
    <nav class="site-container navigation" aria-label="Navegação principal">
        <a class="brand" href="index.php" aria-label="EnsinoTec — início">EnsinoTec</a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="mobile-drawer" aria-label="Abrir menu" hidden>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            <span>Menu</span>
        </button>
        <ul class="navigation-links" id="navigation-links">
            <li>
                <details class="dropdown">
                    <summary>Matérias <span class="chevron" aria-hidden="true"></span></summary>
                    
                    <div class="dropdown-panel subjects-panel">
                        <div class="subjects-heading">
                            <strong>Explore as disciplinas</strong>
                            <span>Escolha uma disciplina para ver seus conteúdos.</span>
                        </div>
                        
                        <ul class="subjects-list" id="navbar-materias">
                            <li class="subjects-message">Carregando disciplinas…</li>
                        </ul>
                        <noscript><p class="subjects-message">Ative o JavaScript para consultar as disciplinas.</p></noscript>
                    </div>
                </details>
            </li>
            <li><a href="sobre.php">Sobre</a></li>
            <li><a href="contato.php">Contato</a></li>
            <li>
                <?php if ($autenticado): ?>
                <details class="dropdown profile">
                    <summary aria-label="Perfil de <?= site_escape($nomeUsuario) ?>">
                        <?php if ($fotoUsuario !== ''): ?>
                        <img class="avatar" src="<?= site_escape($fotoUsuario) ?>" alt="" width="44" height="44">
                        <?php else: ?>
                        <span class="avatar"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 22v-3a8 8 0 0 1 16 0v3"/></svg></span>
                        <?php endif; ?>
                    </summary>
                    <ul class="dropdown-panel">
                        <li><a href="perfil.php">Meu perfil</a></li>
                        <li><a href="configuracoes.php">Configurações</a></li>
                    </ul>
                </details>
                <?php else: ?>
                <a class="sign-in" href="login.php">Entrar</a>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
    <!-- ALTERADO: drawer mobile reutiliza os mesmos links da navbar. -->
    <dialog class="mobile-drawer" id="mobile-drawer" aria-label="Menu EnsinoTec">
        <div class="drawer-heading">
            <span class="brand">EnsinoTec</span>
            <button class="drawer-close" type="button" aria-label="Fechar menu">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
            </button>
        </div>
        <nav class="drawer-content" aria-label="Navegação mobile"></nav>
    </dialog>
</header>
