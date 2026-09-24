<!doctype html>
<html lang="en" data-bs-theme="auto">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="description" content="" />
  <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors" />
  <meta name="generator" content="Astro v5.13.2" />
  <title>Login</title>
  <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/sign-in/" />
  <script src="assets/js/color-modes.js"></script>
  <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet" />
  <meta name="theme-color" content="#712cf9" />
  <link href="assets/css/sign-in.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/Login.css">
</head>

<body class="d-flex align-items-center py-4 bg-body-tertiary">
  <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
    <symbol id="check2" viewBox="0 0 16 16">
      <path
        d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z">
      </path>
    </symbol>
    <symbol id="circle-half" viewBox="0 0 16 16">
      <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"></path>
    </symbol>
    <symbol id="moon-stars-fill" viewBox="0 0 16 16">
      <path
        d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z">
      </path>
      <path
        d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z">
      </path>
    </symbol>
    <symbol id="sun-fill" viewBox="0 0 16 16">
      <path
        d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z">
      </path>
    </symbol>
  </svg>

    <main class="form-signin w-100 m-auto">
    <?php session_start(); ?>

    <?php if (!empty($_SESSION['erro_login'])): ?>
    <div class="alert alert-danger" style="display: none;">
      <?= $_SESSION['erro_login']; ?>
    </div>
    <?php unset($_SESSION['erro_login']); ?>
    <?php endif; ?>

    <form action="login.php" method="POST">
      <img class="mb-4" src="" alt="" width="72" height="57" />
      <h1 class="h3 mb-3 fw-normal">Entre no sistema!</h1>
      <div class="form-floating">
        <input type="Email" name="email" id="email" class="form-control" placeholder="000.000.000-00"
          required />
        <label for="floatingInput">Email</label>
      </div>
      <div class="form-floating">
        <input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="senha" />
        <label for="floatingPassword">Senha</label>
      </div>
      <!-- 2 column grid layout for inline styling -->
      <div class="row mb-4">
        <div class="col d-flex justify-content-center">
          <a href="#!">Esqueceu a sua Senha?</a>
        </div>
      </div>
      <!--  <div class="form-check text-start my-3">
          <input
            class="form-check-input"
            type="checkbox"
            value="remember-me"
            id="checkDefault"
          />
          <label class="form-check-label" for="checkDefault">
            Lembrar-me
          </label>
        </div> -->
      <button class="btn btn-primary w-100 py-2" type="submit">
        Entrar
      </button>
      <div class="text-center mt-3">
        <p>Não tem cadastro? <a href="cadastro.php">Cadastre-se!</a></p>

      </div>
      <p class="mt-5 mb-3 text-body-secondary">&copy; 2026 EnsinoTec - Todos os direitos reservados</p>
    </form>
  </main>

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

  <script src="../assets/dist/js/bootstrap.bundle.min.js" class="astro-vvvwv3sm"></script>
</body>

</html>
