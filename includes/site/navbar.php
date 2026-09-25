<header class="site-header">
    <nav class="site-container navigation" aria-label="Navegação principal">
        <a class="brand" href="index.php" aria-label="NIVELAR — início">EnsinoTec</a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="navigation-links" hidden>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            <span>Menu</span>
        </button>
        <ul class="navigation-links" id="navigation-links">
            <li>
                <details class="dropdown">
                    <summary>Matérias <span class="chevron" aria-hidden="true"></span></summary>
                    <ul class="dropdown-panel">
                        <?php foreach ($materias as $materia): ?>
                        <li><a href="<?= site_escape($materia['url']) ?>"><?= site_escape($materia['titulo']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
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
</header>