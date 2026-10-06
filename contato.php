<?php
// ALTERADO: permite várias linhas no problema, com limite de 1.000 caracteres e contador.
require_once __DIR__ . '/includes/site/auth.php';
header('Cache-Control: no-store');
$nomeContato = $autenticado ? (string) $contaAtual['nome'] : '';


$tituloPagina = 'Contato — EnsinoTec';
$estilosPagina = ['assets/css/contato.css?v=mensagem-2'];
require __DIR__ . '/includes/site/header.php';
?>
<main class="pagina-contato" id="conteudo-principal" tabindex="-1">
    <section class="contato">

        <div class="formulario">

            <h1><span data-i18n="Contate-nos">Contate-nos</span></h1>

            <p class="subtitulo">
                <span data-i18n="Alguma pergunta ou observação? Basta nos">Alguma pergunta ou observação? Basta nos</span><br>
                <span data-i18n="escrever uma mensagem!">escrever uma mensagem!</span>
            </p>


            <form action="#" method="POST">


                <div class="campos">




                    <div class="campo">

                        <label for="nome">
                            <span data-i18n="Nome">Nome</span>
                        </label>

                        <input
                            type="text"
                            id="nome"
                            name="nome"
                            value="<?= site_escape($nomeContato) ?>"
                            placeholder="Entre na sua conta para identificar seu nome"
                            readonly
                            aria-readonly="true">

                    </div>




                    <div class="campo">

                        <label for="problema"><span data-i18n="Problema">Problema</span></label>
                        <textarea id="problema" name="problema" rows="4" maxlength="1000" wrap="soft" placeholder="Diga-nos seu problema" data-i18n-placeholder="Diga-nos seu problema" aria-describedby="problema-limite" required></textarea>
                        <small id="problema-limite" class="contato-contador"><span id="problema-contagem">0</span> / 1.000 caracteres</small>

                    </div>


                </div>




                <?php if (!$autenticado): ?><p><a href="login.php">Entre na sua conta para usar o formulário de contato.</a></p><?php endif; ?>
                <button type="submit" <?= !$autenticado ? 'disabled' : '' ?>>
                    <span data-i18n="ENVIAR">ENVIAR</span>
                </button>


            </form>

        </div>

    </section>





    <section class="informacoes">




        <div class="info">




            <div class="icone">

                <svg
                    viewBox="0 0 64 64"
                    xmlns="http://www.w3.org/2000/svg">



                    <path
                        d="M7 28L32 9L57 28"
                        fill="none"
                        stroke="white"
                        stroke-width="5"
                        stroke-linecap="round"
                        stroke-linejoin="round" />




                    <path
                        d="M12 26V54H52V26"
                        fill="none"
                        stroke="white"
                        stroke-width="5"
                        stroke-linejoin="round" />




                    <path
                        d="M25 54V38H39V54"
                        fill="none"
                        stroke="white"
                        stroke-width="5" />




                    <rect
                        x="18"
                        y="31"
                        width="5"
                        height="5"
                        fill="white" />

                    <rect
                        x="29"
                        y="31"
                        width="5"
                        height="5"
                        fill="white" />

                    <rect
                        x="40"
                        y="31"
                        width="5"
                        height="5"
                        fill="white" />

                </svg>

            </div>


            <h2>
                <span data-i18n="SOBRE A ESCOLA">SOBRE A ESCOLA</span>
            </h2>


            <p>
                <span data-i18n="Educação">Educação</span><br>
                <span data-i18n="Ensino e aprendizagem">Ensino e aprendizagem</span>
            </p>


        </div>





        <div class="info">




            <div class="icone">

                <svg
                    viewBox="0 0 64 64"
                    xmlns="http://www.w3.org/2000/svg">

                    <path
                        d="M32 7
                           C21 7 13 16 13 27
                           C13 41 32 56 32 56
                           C32 56 51 41 51 27
                           C51 16 43 7 32 7Z"
                        fill="white" />

                    <circle
                        cx="32"
                        cy="27"
                        r="7"
                        fill="#e00000" />

                </svg>

            </div>


            <h2>
                <span data-i18n="ENDEREÇO">ENDEREÇO</span>
            </h2>


            <p>
                Rua Lúcio Sartori, 809<br>
                Bebedouro - SP<br>
                CEP: 14706-120
            </p>


        </div>





        <div class="info">




            <div class="icone">

                <svg
                    viewBox="0 0 64 64"
                    xmlns="http://www.w3.org/2000/svg">

                    <path
                        d="M20 10
                           C17 10 14 13 14 17
                           C14 36 28 51 47 51
                           C51 51 54 48 54 44
                           L54 37
                           C54 35 52 33 50 33
                           L42 31
                           C40 31 38 32 37 34
                           L35 39
                           C29 36 24 31 22 25
                           L27 23
                           C29 22 29 20 28 18
                           L24 12
                           C23 11 21 10 20 10Z"
                        fill="white" />

                </svg>

            </div>


            <h2>
                <span data-i18n="TELEFONES">TELEFONES</span>
            </h2>


            <p>
                (17) 3344-9688<br>
                (17) 98842-4140
            </p>


        </div>


    </section>
</main>
<script src="assets/js/contato.js" defer></script>
<?php require __DIR__ . '/includes/site/footer.php'; ?>