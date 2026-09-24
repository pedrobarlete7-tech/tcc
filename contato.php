<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contato - Escola</title>

    <link rel="stylesheet" href="assets/css/contato.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">

</head>

<body>

	<!-- BOTÃO VOLTAR -->
<a href="index.php" class="btn-voltar">
    ← Voltar
</a>


    <!-- =====================================
         ÁREA DE CONTATO
    ====================================== -->

    <section class="contato">

        <div class="formulario">

            <h1>Contate-nos</h1>

            <p class="subtitulo">
                Alguma pergunta ou observação? Basta nos<br>
                escrever uma mensagem!
            </p>


            <form action="#" method="POST">


                <div class="campos">


                    <!-- EMAIL -->

                    <div class="campo">

                        <label for="text">
                            Nome
                        </label>

                        <input
                            type="text"
                            id="nome"
                            name="nome"
                            placeholder="Diga-nos seu nome"
                            required
                        >

                    </div>


                    <!-- PROBLEMA -->

                    <div class="campo">

                        <label for="email">
                            Problema
                        </label>

						    <input
                            type="text"
                            id="problema"
                            name="problema"
                            placeholder="Diga-nos seu problema"
                            required
                        >

                    </div>


                </div>


                <!-- BOTÃO -->

                <button type="submit">
                    ENVIAR
                </button>


            </form>

        </div>

    </section>



    <!-- =====================================
         INFORMAÇÕES DA ESCOLA
    ====================================== -->

    <section class="informacoes">


        <!-- =================================
             SOBRE A ESCOLA
        ================================== -->

        <div class="info">


            <!-- ÍCONE -->

            <div class="icone">

                <svg
                    viewBox="0 0 64 64"
                    xmlns="http://www.w3.org/2000/svg"
                >

                    <!-- Telhado -->

                    <path
                        d="M7 28L32 9L57 28"
                        fill="none"
                        stroke="white"
                        stroke-width="5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />


                    <!-- Prédio -->

                    <path
                        d="M12 26V54H52V26"
                        fill="none"
                        stroke="white"
                        stroke-width="5"
                        stroke-linejoin="round"
                    />


                    <!-- Porta -->

                    <path
                        d="M25 54V38H39V54"
                        fill="none"
                        stroke="white"
                        stroke-width="5"
                    />


                    <!-- Janelas -->

                    <rect
                        x="18"
                        y="31"
                        width="5"
                        height="5"
                        fill="white"
                    />

                    <rect
                        x="29"
                        y="31"
                        width="5"
                        height="5"
                        fill="white"
                    />

                    <rect
                        x="40"
                        y="31"
                        width="5"
                        height="5"
                        fill="white"
                    />

                </svg>

            </div>


            <h2>
                SOBRE A ESCOLA
            </h2>


            <p>
                Educação<br>
                Ensino e aprendizagem
            </p>


        </div>



        <!-- =================================
             ENDEREÇO
        ================================== -->

        <div class="info">


            <!-- ÍCONE DE LOCALIZAÇÃO -->

            <div class="icone">

                <svg
                    viewBox="0 0 64 64"
                    xmlns="http://www.w3.org/2000/svg"
                >

                    <path
                        d="M32 7
                           C21 7 13 16 13 27
                           C13 41 32 56 32 56
                           C32 56 51 41 51 27
                           C51 16 43 7 32 7Z"
                        fill="white"
                    />

                    <circle
                        cx="32"
                        cy="27"
                        r="7"
                        fill="#e00000"
                    />

                </svg>

            </div>


            <h2>
                ENDEREÇO
            </h2>


            <p>
                Rua Lúcio Sartori, 809<br>
                Bebedouro - SP<br>
                CEP: 14706-120
            </p>


        </div>



        <!-- =================================
             TELEFONES
        ================================== -->

        <div class="info">


            <!-- ÍCONE DE TELEFONE -->

            <div class="icone">

                <svg
                    viewBox="0 0 64 64"
                    xmlns="http://www.w3.org/2000/svg"
                >

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
                        fill="white"
                    />

                </svg>

            </div>


            <h2>
                TELEFONES
            </h2>


            <p>
                (17) 3344-9688<br>
                (17) 98842-4140
            </p>


        </div>


    </section>



    <!-- =====================================
         FAIXA VERMELHA
    ====================================== -->

    <div class="faixa"></div>


</body>

</html>