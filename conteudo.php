<?php
// Conexão real do projeto (PDO) - disponibiliza a variável $pdo
require_once __DIR__ . '/actions/conexao.php';

$idConteudo = filter_input(INPUT_GET, 'id_conteudo', FILTER_VALIDATE_INT);

if (!$idConteudo) {
    http_response_code(400);
    die('Conteúdo não especificado.');
}

// Busca o conteúdo + nome da matéria
$stmt = $pdo->prepare(
    'SELECT c.id_conteudo, c.titulo, c.texto, c.nivel_dificuldade, m.titulo AS materia_titulo
     FROM conteudo c
     JOIN materia m ON m.id_materia = c.id_materia
     WHERE c.id_conteudo = ?'
);
$stmt->execute([$idConteudo]);
$conteudo = $stmt->fetch();

if (!$conteudo) {
    http_response_code(404);
    die('Conteúdo não encontrado.');
}

// Busca o resumo (imagem) associado
$stmt = $pdo->prepare('SELECT caminho_imagem, descricao FROM resumo WHERE id_conteudo = ?');
$stmt->execute([$idConteudo]);
$resumo = $stmt->fetch();

// Busca vídeo(s) associado(s)
$stmt = $pdo->prepare('SELECT titulo, url_video FROM video WHERE id_conteudo = ?');
$stmt->execute([$idConteudo]);
$videos = $stmt->fetchAll();

// Busca exercícios + alternativas
$stmt = $pdo->prepare('SELECT id_exercicio, pergunta FROM exercicio WHERE id_conteudo = ?');
$stmt->execute([$idConteudo]);
$exercicios = $stmt->fetchAll();

foreach ($exercicios as &$exercicio) {
    $stmtAlt = $pdo->prepare('SELECT texto, correta FROM alternativa WHERE id_exercicio = ?');
    $stmtAlt->execute([$exercicio['id_exercicio']]);
    $exercicio['alternativas'] = $stmtAlt->fetchAll();
}
unset($exercicio);
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($conteudo['titulo']) ?> - TCC 2026</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/conteudo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body class="bg-body text-body">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light py-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="home.php"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav_lc"
                aria-controls="nav_lc" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="nav_lc">
                <ul class="navbar-nav my-3 my-lg-0 ms-lg-3 me-auto">
                    <li class="nav-item me-4"><a class="nav-link" href="index.php">Início</a></li>
                    <li class="nav-item me-4"><a class="nav-link" href="sobre.php">Sobre</a></li>
                    <li class="nav-item"><a class="nav-link" href="contato.php">Contato</a></li>
                </ul>

                <!-- Menu Lateral -->
                <input type="checkbox" id="menu-toggle">
                <label for="menu-toggle" class="menu-btn">
                    <span></span>
                    <span></span>
                    <span></span>
                </label>

                <div class="slide-menu" id="menu-lateral">
                    <ul>
                        <!-- renderizado por materias.js -->
                    </ul>
                </div>

                <div class="overlay"></div>
                <!-- Fim menu Lateral -->

                <!-- Menu lateral direita -->
                <div class="perfil">
                    <a class="btn btn-outline-secondary me-2" id="entrar" href="login.php">Entrar</a>
                    <input type="checkbox" id="menu-toggle-right">
                    <label for="menu-toggle-right" class="menu-btn-right">
                        <i class="fas fa-user-circle"></i>
                    </label>

                    <div class="slide-menu-right">
                        <ul>
                            <li><a href="configuracoes.php">Configurações</a></li>
                            <li><a href="#">Sair</a></li>
                        </ul>
                    </div>

                    <div class="overlay-right"></div>
                </div>
            </div>
        </div>
    </nav>
    <!-- FIM NAVBAR -->

    <main class="conteudo">

        <h1><?= htmlspecialchars($conteudo['titulo']) ?></h1>

        <div class="resumo">
            <h3><?= htmlspecialchars($conteudo['materia_titulo']) ?> · Nível <?= htmlspecialchars($conteudo['nivel_dificuldade']) ?></h3>
            <?php if ($resumo): ?>
                <p><?= htmlspecialchars($resumo['descricao']) ?></p>
            <?php endif; ?>
        </div>

        <?php if ($resumo && !empty($resumo['caminho_imagem'])): ?>
            <div class="imagem">
                <img src="<?= htmlspecialchars($resumo['caminho_imagem']) ?>" alt="<?= htmlspecialchars($conteudo['titulo']) ?>">
            </div>
        <?php endif; ?>

        <div class="texto-grande">
            <h2>Explicação</h2>
            <p><?= nl2br(htmlspecialchars($conteudo['texto'])) ?></p>
        </div>

        <?php if (!empty($videos)): ?>
            <div class="video">
                <h2>Vídeo</h2>
                <?php foreach ($videos as $video): ?>
                    <div class="caixa-video">
                        <p><?= htmlspecialchars($video['titulo']) ?></p>
                        <a href="<?= htmlspecialchars($video['url_video']) ?>" target="_blank" rel="noopener noreferrer">
                            Assistir vídeo
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="exercicios">
            <h2>Exercícios</h2>
            <?php if (!empty($exercicios)): ?>
                <?php foreach ($exercicios as $exercicio): ?>
                    <div class="questao">
                        <p><strong><?= htmlspecialchars($exercicio['pergunta']) ?></strong></p>
                        <ul>
                            <?php foreach ($exercicio['alternativas'] as $alt): ?>
                                <li><?= htmlspecialchars($alt['texto']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="sem-exercicios">Nenhum exercício disponível para este conteúdo ainda.</p>
            <?php endif; ?>
        </div>

    </main>

    <!-- FOOTER -->
    <section>
        <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top">
            <div class="col-md-4 d-flex align-items-center">
                <div class="lc-block text-center mb-3 mb-md-0">
                    <span class="text-muted">© 2026 TCC, Projeto Integrado</span>
                </div>
            </div>
        </div>
    </section>
    <!-- Fim Footer -->

    <button id="toggleTheme" class="floating-theme-btn">
        <i class="bi bi-moon-stars-fill"></i>
    </button>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const button = document.getElementById('toggleTheme');
            const html = document.documentElement;
            const icon = button.querySelector('i');

            function applyTheme(theme) {
                html.setAttribute('data-bs-theme', theme);
                icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
            }

            const savedTheme = localStorage.getItem('theme') || 'light';
            applyTheme(savedTheme);

            button.addEventListener('click', function () {
                const currentTheme = html.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                localStorage.setItem('theme', newTheme);
                applyTheme(newTheme);
            });
        });
    </script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="./assets/js/materias.js"></script>
</body>

</html>