<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>cadastro</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/cad.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

  <div class="caixa">
    <h2>Cadastre-se</h2>
    <form class="row g-3 needs-validation" method="POST" novalidate>
      <div class="campoNome">

        <input type="text" class="form-control" id="validationCustom01" placeholder="Insira seu nome completo" required>
        <div class="invalid-feedback">
          Este campo tem que conter seu nome.
        </div>
      </div>

      <div class="campoEmail">

        <div class="input-group has-validation">
          <input type="email" class="form-control" id="validationCustomUsername" aria-describedby="inputGroupPrepend"
            placeholder="Insira seu E-mail" minlength="6" required>
          <div class="invalid-feedback">
            Por favor coloque um E-mail válido.
          </div>
        </div>
      </div>
      <div class="campoSenha">

        <input type="password" class="form-control" id="senha" placeholder="Insira sua senha" minlength="6" required>
        <div class="invalid-feedback">
          Informe uma senha com pelo menos 6 caracteres.
        </div>
      </div>
      <div class="confirmaSenha">

        <input type="password" class="form-control" id="confirmar_senha" placeholder="Confirme a sua senha!" required>
        <div class="VerificarSenha"></div>
        <div class="invalid-feedback">
          Confirme a senha.
        </div>
      </div>

      <div class="col-12">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="" id="aceiteTermos" required>
          <label class="form-check-label" for="aceiteTermos">
            Li e aceito os <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalTermos">termos de uso</a>
          </label>
          <div class="invalid-feedback">
            Você precisa aceitar os termos de uso para se cadastrar.
          </div>
        </div>
      </div>

      <div class="col-12">
        <button class="btn btn-primary" type="submit" id="bcad">Cadastrar</button>
      </div>

      <p class="mt-5 mb-3 text-body-secondary">&copy; 2026 EnsinoTec - Todos os direitos reservados</p>
    </form>

    <!-- Modal Termos de Uso -->
    <div class="modal fade" id="modalTermos" tabindex="-1" aria-labelledby="modalTermosLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalTermosLabel">Termos de Uso</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
          </div>
          <div class="modal-body">
            <p><strong>1. Aceitação dos Termos</strong><br>
            Ao acessar e utilizar esta plataforma de nivelamento de matemática, o usuário concorda com os presentes Termos de Uso e com todas as condições aqui estabelecidas. Caso não concorde com algum termo, recomenda-se não utilizar o sistema.</p>

            <p><strong>2. Objetivo da Plataforma</strong><br>
            A plataforma tem como finalidade auxiliar estudantes no aprendizado e nivelamento de conteúdos matemáticos, oferecendo materiais educativos, exercícios, avaliações e recursos de apoio ao estudo.</p>

            <p><strong>3. Cadastro Obrigatório</strong><br>
            Para utilizar a plataforma e acessar suas funcionalidades, é obrigatório realizar um cadastro com informações verdadeiras, completas e atualizadas.<br>
            O usuário é responsável por:</p>
            <ul>
              <li>Manter a confidencialidade de sua conta e senha;</li>
              <li>Garantir a veracidade das informações fornecidas;</li>
              <li>Não compartilhar sua conta com terceiros;</li>
              <li>Informar imediatamente qualquer uso não autorizado de sua conta.</li>
            </ul>
            <p>Sem o cadastro, não será possível acessar os recursos, atividades e conteúdos disponíveis na plataforma.</p>

            <p><strong>4. Uso Adequado da Plataforma</strong><br>
            O usuário compromete-se a utilizar a plataforma de forma ética e responsável, sendo proibido:</p>
            <ul>
              <li>Compartilhar conteúdos ofensivos, ilegais ou inadequados;</li>
              <li>Tentar acessar áreas restritas do sistema sem autorização;</li>
              <li>Utilizar a plataforma para prejudicar outros usuários;</li>
              <li>Copiar, modificar ou distribuir conteúdo sem autorização.</li>
            </ul>

            <p><strong>5. Propriedade Intelectual</strong><br>
            Todos os conteúdos presentes na plataforma, incluindo textos, imagens, atividades, logotipos e materiais didáticos, são protegidos por direitos autorais e pertencem aos desenvolvedores do projeto ou aos respectivos autores.</p>

            <p><strong>6. Privacidade e Dados</strong><br>
            As informações fornecidas pelos usuários serão utilizadas apenas para fins acadêmicos e de funcionamento da plataforma, respeitando a legislação vigente de proteção de dados.</p>

            <p><strong>7. Limitação de Responsabilidade</strong><br>
            A plataforma busca oferecer conteúdos corretos e atualizados, porém não garante ausência total de erros ou interrupções no sistema.<br>
            Os desenvolvedores não se responsabilizam por:</p>
            <ul>
              <li>Problemas causados por mau uso da plataforma;</li>
              <li>Falhas de conexão com a internet;</li>
              <li>Perda de dados ocasionada por fatores externos.</li>
            </ul>

            <p><strong>8. Alterações nos Termos</strong><br>
            Os presentes Termos de Uso poderão ser modificados a qualquer momento para melhoria da plataforma. Recomenda-se que o usuário consulte esta página periodicamente.</p>

            <p><strong>9. Encerramento de Conta</strong><br>
            A administração poderá suspender ou encerrar contas que violem estes Termos de Uso ou pratiquem atividades prejudiciais ao funcionamento da plataforma.</p>

            <p><strong>10. Contato</strong><br>
            Em caso de dúvidas, sugestões ou problemas relacionados à plataforma, o usuário poderá entrar em contato com a equipe responsável pelo projeto.<br>
            E-mail para contato: etecbebedouroinfo@gmail.com</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Fim Modal Termos de Uso -->

    <button id="toggleTheme" class="floating-theme-btn">
      <i class="bi bi-moon-stars-fill"></i>
    </button>
    <!-- Fluid theme -->


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
    <!-- Fim fluid theme -->

    <!-- Script erro -->

    <script>
      (() => {
        'use strict';

        const forms = document.querySelectorAll('.needs-validation');

        Array.from(forms).forEach(form => {
          form.addEventListener('submit', event => {
            const senha = document.getElementById('senha').value;
            const confirmarSenha = document.getElementById('confirmar_senha').value;

            if (!form.checkValidity() || senha !== confirmarSenha) {
              event.preventDefault();
              event.stopPropagation();

              if (senha !== confirmarSenha) {
                document.getElementById('confirmar_senha').setCustomValidity('As senhas não coincidem.');
              } else {
                document.getElementById('confirmar_senha').setCustomValidity('');
              }
            } else {
              document.getElementById('confirmar_senha').setCustomValidity('');
            }

            form.classList.add('was-validated');
          }, false);
        });
      })();
    </script>
    <script>
      const senha = document.getElementById('senha');
      const confirmarSenha = document.getElementById('confirmar_senha');

      confirmarSenha.addEventListener('input', () => {

        if (senha.value !== confirmarSenha.value) {
          confirmarSenha.setCustomValidity('As senhas não coincidem');
        } else {
          confirmarSenha.setCustomValidity('');
        }
        const formulario = document.getElementById('bcad');

      });
    </script>

    <!-- Fim Script eroo -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
  </div>
</body>

</html>