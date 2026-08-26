<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/suporte.css">
    <link rel="stylesheet" href="assets/css/sign-in.css">
    <title>suporte</title>
</head>

<body>
    <div class="sup" id="sup">
        <form action="home.html" method="post">
               <a class="btn-voltar" href="home.html">
            ← VOLTAR
        </a>
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" placeholder="Digite seu nome">

            <label for="email">E-mail:</label>
            <input type="email" class="form-control" id="validationCustomUsername" aria-describedby="inputGroupPrepend"
            placeholder="Insira seu E-mail" minlength="6" required>

            <label for="problema">Problema:</label>
            <input type="text" id="problema" name="problema" placeholder="Digite o problema">
            <button class="btn btn-danger" type="submit">
                Enviar
            </button>
        </form>
    </div>
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

                icon.className = theme === 'dark' ?
                    'bi bi-sun-fill' :
                    'bi bi-moon-stars-fill';
            }

            // Carregar tema salvo
            const savedTheme = localStorage.getItem('theme') || 'light';
            applyTheme(savedTheme);

            // Clique
            button.addEventListener('click', function () {
                const currentTheme = html.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

                localStorage.setItem('theme', newTheme);
                applyTheme(newTheme);
            });
        });
    </script>

</body>

</html>