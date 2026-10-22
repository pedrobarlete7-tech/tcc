<?php
// ALTERADO: organiza os grupos em cards com cabeçalho e lista de integrantes.


$tituloPagina = 'Sobre — EnsinoTec';
$estilosPagina = ['assets/css/sobre.css?v=cards-20261008'];
require __DIR__ . '/includes/site/header.php';
?>
    <main class="sobre" id="conteudo-principal" tabindex="-1">
        <h1 class="titulo"><span data-i18n="Sobre o Projeto">Sobre o Projeto</span></h1>

        <div class="proposito">
            <h2><span data-i18n="Propósito">Propósito</span></h2>
            <p>
                O propósito do EnsinoTec é oferecer um ambiente educacional que auxilie estudantes na revisão e no reforço dos conhecimentos adquiridos durante o Ensino Fundamental II, especialmente na transição para o Ensino Médio. A plataforma busca reunir conteúdos e recursos didáticos de maneira organizada, simples e acessível, proporcionando diferentes formas de estudo por meio de resumos, mapas mentais, vídeos educacionais e exercícios. Atualmente, o EnsinoTec tem como foco a Matemática, com conteúdos fundamentais para a continuidade da aprendizagem. Futuramente, pretende-se disponibilizar também as demais matérias do Ensino Fundamental II, ampliando as possibilidades de estudo e tornando a plataforma um espaço de apoio para diferentes áreas do conhecimento.
            </p>
        </div>

        <div class="historia">
            <div class="historia_texto">
                <h2><span data-i18n="História de origem">História de origem</span></h2>
                <p>
                    EXEMPLO EXEMPLO EXEMPLO EXEMPLO EXEMPLO EXEMPLO EXEMPLO
                    EXEMPLO EXEMPLO EXEMPLO EXEMPLO EXEMPLO EXEMPLO EXEMPLO.
                </p>
            </div>
            <div class="historia_video">
                <video src=""></video>
            </div>
        </div>

        <div class="escola">
            <h2><span data-i18n="Como Operamos">Como Operamos</span></h2>
            <p>
                EXEMPLO EXEMPLO EXEMPLO EXEMPLO EXEMPLO EXEMPLO EXEMPLO
                EXEMPLO EXEMPLO EXEMPLO EXEMPLO EXEMPLO EXEMPLO EXEMPLO.
            </p>
        </div>

        <div class="pessoas">
            <h2>
                Grupos dos desenvolvedores e os coorientadores, juntamento do Professor de TCC; Rodrigo Bruno Kehdy.
            </h2>
            <article class="equipe">
                <header class="equipe-cabecalho"><span class="equipe-numero" aria-hidden="true">01</span><h3><span data-i18n="front-end usuário">front-end usuário</span></h3></header>
                <ul class="equipe-integrantes">
                    <li translate="no">Enzo Moteiro</li>
                    <li translate="no">Felipe Eduardo Guessi</li>
                    <li translate="no">José Augusto Freitas</li>
                    <li translate="no">João Pedro Ferreira Petri</li>
                </ul>
            </article>
            <article class="equipe">
                <header class="equipe-cabecalho"><span class="equipe-numero" aria-hidden="true">02</span><h3><span data-i18n="front-end administrador">front-end administrador</span></h3></header>
                <ul class="equipe-integrantes">
                    <li translate="no">Mateus Antonioli</li>
                    <li translate="no">Matheus Osório</li>
                    <li translate="no">Pedro Barlete Lopes</li>
                </ul>
            </article>
            <article class="equipe">
                <header class="equipe-cabecalho"><span class="equipe-numero" aria-hidden="true">03</span><h3><span data-i18n="Banco de Dados">Banco de Dados</span></h3></header>
                <ul class="equipe-integrantes">
                    <li translate="no">Fabio Francisco Baricordi Novas</li>
                    <li translate="no">Jonathas Gabriel dos Santos</li>
                    <li translate="no">Lays de Oliveira Almeida</li>
                    <li translate="no">Maria Eduarda Camila da Silva</li>
                </ul>
            </article>
            <article class="equipe">
                <header class="equipe-cabecalho"><span class="equipe-numero" aria-hidden="true">04</span><h3><span data-i18n="desenvolvimento, pesquisa e introdução">desenvolvimento, pesquisa e introdução</span></h3></header>
                <ul class="equipe-integrantes">
                    <li translate="no">Kamilly Capucho Aguiar</li>
                    <li translate="no">Luana Leite Evangelista</li>
                    <li translate="no">Pietro Limão Rosa</li>
                    <li translate="no">Ysis Fernandes de Souza</li>
                </ul>
            </article>
            <article class="equipe">
                <header class="equipe-cabecalho"><span class="equipe-numero" aria-hidden="true">05</span><h3><span data-i18n="pesquisa e desenvolvimento">pesquisa e desenvolvimento</span></h3></header>
                <ul class="equipe-integrantes">
                    <li translate="no">Emanuelle Vitória Da Silva</li>
                    <li translate="no">João Pedro Chagas Izaias</li>
                    <li translate="no">Felipe Dias do Nascimento</li>
                    <li translate="no">Jóse Vinicius Balsaneli</li>
                    <li translate="no">Vinicius Balsanelli</li>
                </ul>
            </article>
            <article class="equipe">
                <header class="equipe-cabecalho"><span class="equipe-numero" aria-hidden="true">06</span><h3><span data-i18n="Teste de Desempenho">Teste de Desempenho</span></h3></header>
                <ul class="equipe-integrantes">
                    <li translate="no">Luiz Gabriel Arribard Da Silva</li>
                    <li translate="no">Yosef Miguel De Lima</li>
                    <li translate="no">Yuri Camara Soares</li>
                </ul>
            </article>
            <article class="equipe">
                <header class="equipe-cabecalho"><span class="equipe-numero" aria-hidden="true">07</span><h3><span data-i18n="Cronograma">Cronograma</span></h3></header>
                <ul class="equipe-integrantes">
                    <li translate="no">Carlos Daniel Chiarelli</li>
                    <li translate="no">Estevan Baccas Masotti</li>
                    <li translate="no">Guilherme dos Santos Ferreira</li>
                    <li translate="no">Gustavo dos Santos Ferreira</li>
                </ul>
            </article>
        </div>
        <div class="porque">
            <h2><span data-i18n="Por que utilizar o EnsinoTec?">Por que utilizar o EnsinoTec?</span></h2>
            <p>
                O EnsinoTec foi desenvolvido para auxiliar estudantes na revisão de conteúdos de Matemática de forma simples, organizada e acessível. A plataforma reúne diferentes recursos educacionais em um único ambiente, permitindo que o aluno encontre materiais de apoio para seus estudos com maior facilidade. Por meio de resumos, mapas mentais, vídeos educacionais e exercícios, o EnsinoTec oferece diferentes formas de revisar os conteúdos, possibilitando que cada estudante utilize os recursos que melhor contribuam para seus estudos. Atualmente, a plataforma tem como foco conteúdos matemáticos do Ensino Fundamental II, auxiliando principalmente estudantes do primeiro ano do Ensino Médio que desejam retomar conhecimentos importantes para a continuidade da aprendizagem. Futuramente, pretende-se disponibilizar conteúdos das demais matérias do Ensino Fundamental II, ampliando a variedade de materiais educacionais disponíveis na plataforma e oferecendo apoio aos estudos em diferentes áreas do conhecimento.
            </p>
        </div>

    </main>
<?php require __DIR__ . '/includes/site/footer.php'; ?>