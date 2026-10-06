-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 06/10/2026 às 16:56
-- Versão do servidor: 10.11.14-MariaDB-0ubuntu0.24.04.1
-- Versão do PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `tcc`
--

DELIMITER $$
--
-- Procedimentos
--
CREATE DEFINER=`tcc_user`@`localhost` PROCEDURE `usp_atualizar_alternativa` (IN `p_id_alternativa` INT, IN `p_texto` TEXT, IN `p_correta` TINYINT(1))   BEGIN

    UPDATE alternativa
    SET
        texto = p_texto,
        correta = p_correta
    WHERE id_alternativa = p_id_alternativa$$

CREATE DEFINER=`tcc_user`@`localhost` PROCEDURE `usp_atualizar_aluno` (IN `p_id_usuario` INT(11), IN `p_nome` VARCHAR(100), IN `p_email` VARCHAR(150), IN `p_senha` VARCHAR(225), IN `p_tipo_usuario` ENUM('aluno','professor','administrador'), IN `p_ativo` TINYINT(1), IN `p_data_cadastro` DATETIME)   BEGIN

    UPDATE usuario
    SET
        nome = p_nome,
        email = p_email,
        senha = p_senha,
        tipo_usuario = p_tipo_usuario,
        ativo = p_ativo,
        data_cadastro = p_data_cadastro
    WHERE id_usuario = p_id_usuario$$

CREATE DEFINER=`tcc_user`@`localhost` PROCEDURE `usp_atualizar_conteudo` (IN `p_id_conteudo` INT, IN `p_titulo` VARCHAR(100), IN `p_texto` TEXT, IN `p_ordem` INT, IN `p_nivel_dificuldade` ENUM('facil','medio','avancado'))   BEGIN

    UPDATE conteudo
    SET
        titulo = p_titulo,
        texto = p_texto,
        ordem = p_ordem,
        nivel_dificuldade = p_nivel_dificuldade
    WHERE id_conteudo = p_id_conteudo$$

CREATE DEFINER=`tcc_user`@`localhost` PROCEDURE `usp_atualizar_exercicio` (IN `p_id_exercicio` INT, IN `p_pergunta` TEXT)   BEGIN

    UPDATE exercicio
    SET
        pergunta = p_pergunta
    WHERE id_exercicio = p_id_exercicio$$

CREATE DEFINER=`tcc_user`@`localhost` PROCEDURE `usp_atualizar_materia` (IN `p_id_materia` INT, IN `p_titulo` VARCHAR(100), IN `p_descricao` TEXT)   BEGIN

    UPDATE materia
    SET
        titulo = p_titulo,
        descricao = p_descricao
    WHERE id_materia = p_id_materia$$

CREATE DEFINER=`tcc_user`@`localhost` PROCEDURE `usp_atualizar_resumo` (IN `p_id_resumo` INT, IN `p_caminho_imagem` VARCHAR(255), IN `p_descricao` TEXT)   BEGIN

    UPDATE resumo
    SET
        caminho_imagem = p_caminho_imagem,
        descricao = p_descricao
    WHERE id_resumo = p_id_resumo$$

CREATE DEFINER=`tcc_user`@`localhost` PROCEDURE `usp_atualizar_video` (IN `p_id_video` INT, IN `p_titulo` VARCHAR(100), IN `p_url` VARCHAR(255))   BEGIN

    UPDATE video
    SET
        titulo = p_titulo,
        url = p_url
    WHERE id_video = p_id_video$$

CREATE DEFINER=`tcc_user`@`localhost` PROCEDURE `usp_cadastrar_alternativa` (IN `p_id_exercicio` INT, IN `p_texto` TEXT, IN `p_correta` TINYINT(1))   BEGIN

    INSERT INTO alternativa(
        id_exercicio,
        texto,
        correta
    )
    VALUES(
        p_id_exercicio,
        p_texto,
        p_correta
    )$$

CREATE DEFINER=`tcc_user`@`localhost` PROCEDURE `usp_cadastrar_aluno` (IN `p_nome` VARCHAR(100), IN `p_email` VARCHAR(150), IN `p_senha` VARCHAR(225), IN `p_tipo_usuario` ENUM('aluno','professor','administrador'), IN `p_ativo` TINYINT(1), IN `p_data_cadastro` DATETIME)   BEGIN

    INSERT INTO usuario(
        nome,
        email,
        senha,
        tipo_usuario,
        ativo,
        data_cadastro
    )
    VALUES (
        p_nome,
        p_email,
        p_senha,
        p_tipo_usuario,
        p_ativo,
        p_data_cadastro
    )$$

CREATE DEFINER=`tcc_user`@`localhost` PROCEDURE `usp_cadastrar_conteudo` (IN `p_id_materia` INT, IN `p_titulo` VARCHAR(100), IN `p_texto` TEXT, IN `p_ordem` INT, IN `p_nivel_dificuldade` ENUM('facil','medio','avancado'))   BEGIN

    INSERT INTO conteudo(
        id_materia,
        titulo,
        texto,
        ordem,
        nivel_dificuldade
    )
    VALUES(
        p_id_materia,
        p_titulo,
        p_texto,
        p_ordem,
        p_nivel_dificuldade
    )$$

CREATE DEFINER=`tcc_user`@`localhost` PROCEDURE `usp_cadastrar_exercicio` (IN `p_id_conteudo` INT, IN `p_pergunta` TEXT)   BEGIN

    INSERT INTO exercicio(
        id_conteudo,
        pergunta
    )
    VALUES(
        p_id_conteudo,
        p_pergunta
    )$$

CREATE DEFINER=`tcc_user`@`localhost` PROCEDURE `usp_cadastrar_materia` (IN `p_titulo` VARCHAR(100), IN `p_descricao` TEXT)   BEGIN

    INSERT INTO materia(
        titulo,
        descricao
    )
    VALUES(
        p_titulo,
        p_descricao
    )$$

CREATE DEFINER=`tcc_user`@`localhost` PROCEDURE `usp_cadastrar_resumo` (IN `p_id_conteudo` INT, IN `p_caminho_imagem` VARCHAR(255), IN `p_descricao` TEXT)   BEGIN

    INSERT INTO resumo(
        id_conteudo,
        caminho_imagem,
        descricao
    )
    VALUES(
        p_id_conteudo,
        p_caminho_imagem,
        p_descricao
    )$$

CREATE DEFINER=`tcc_user`@`localhost` PROCEDURE `usp_cadastrar_video` (IN `p_titulo` VARCHAR(100), IN `p_url` VARCHAR(255))   BEGIN

    INSERT INTO video(
        titulo,
        url
    )
    VALUES(
        p_titulo,
        p_url
    )$$

CREATE DEFINER=`tcc_user`@`localhost` PROCEDURE `usp_deletar_alternativa` (IN `p_id_alternativa` INT)   BEGIN

    DELETE FROM alternativa
    WHERE id_alternativa = p_id_alternativa$$

CREATE DEFINER=`tcc_user`@`localhost` PROCEDURE `usp_deletar_aluno` (IN `p_id_usuario` INT(11))   BEGIN

    DELETE FROM usuario
    WHERE id_usuario = p_id_usuario$$

CREATE DEFINER=`tcc_user`@`localhost` PROCEDURE `usp_deletar_conteudo` (IN `p_id_conteudo` INT)   BEGIN

    DELETE FROM conteudo
    WHERE id_conteudo = p_id_conteudo$$

CREATE DEFINER=`tcc_user`@`localhost` PROCEDURE `usp_deletar_exercicio` (IN `p_id_exercicio` INT)   BEGIN

    DELETE FROM exercicio
    WHERE id_exercicio = p_id_exercicio$$

CREATE DEFINER=`tcc_user`@`localhost` PROCEDURE `usp_deletar_materia` (IN `p_id_materia` INT)   BEGIN

    DELETE FROM materia
    WHERE id_materia = p_id_materia$$

CREATE DEFINER=`tcc_user`@`localhost` PROCEDURE `usp_deletar_resumo` (IN `p_id_resumo` INT)   BEGIN

    DELETE FROM resumo
    WHERE id_resumo = p_id_resumo$$

CREATE DEFINER=`tcc_user`@`localhost` PROCEDURE `usp_deletar_video` (IN `p_id_video` INT)   BEGIN

    DELETE FROM video
    WHERE id_video = p_id_video$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `alternativa`
--

CREATE TABLE `alternativa` (
  `id_alternativa` int(11) NOT NULL,
  `id_exercicio` int(11) NOT NULL,
  `texto` varchar(255) NOT NULL,
  `correta` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `alternativa`
--

INSERT INTO `alternativa` (`id_alternativa`, `id_exercicio`, `texto`, `correta`) VALUES
(1, 1, '40', 0),
(2, 1, '42', 0),
(3, 1, '44', 1),
(4, 1, '46', 0),
(5, 2, '56', 0),
(6, 2, '64', 0),
(7, 2, '68', 1),
(8, 2, '72', 0),
(9, 3, '10', 0),
(10, 3, '12', 1),
(11, 3, '14', 0),
(12, 3, '16', 0),
(13, 4, '28 reais', 0),
(14, 4, '30 reais', 0),
(15, 4, '32 reais', 1),
(16, 4, '38 reais', 0),
(17, 5, '1/2', 0),
(18, 5, '3/4', 0),
(19, 5, '1', 1),
(20, 5, '5/4', 0),
(21, 6, '15', 0),
(22, 6, '16', 0),
(23, 6, '17', 1),
(24, 6, '18', 0),
(25, 7, '24 cm', 0),
(26, 7, '28 cm', 0),
(27, 7, '32 cm', 1),
(28, 7, '36 cm', 0),
(29, 9, '5', 0),
(30, 9, '6', 0),
(31, 9, '7', 1),
(32, 9, '8', 0),
(33, 10, '20%', 0),
(34, 10, '25%', 1),
(35, 10, '30%', 0),
(36, 10, '35%', 0),
(37, 12, '25', 0),
(38, 12, '27', 0),
(39, 12, '29', 1),
(40, 12, '31', 0),
(41, 13, '20 cm²', 0),
(42, 13, '25 cm²', 0),
(43, 13, '30 cm²', 1),
(44, 13, '60 cm²', 0),
(45, 14, '56 reais', 0),
(46, 14, '60 reais', 0),
(47, 14, '64 reais', 1),
(48, 14, '72 reais', 0),
(49, 15, '9', 0),
(50, 15, '10', 0),
(51, 15, '11', 1),
(52, 15, '12', 0),
(53, 8, '50 cm²', 0),
(54, 8, '55 cm²', 0),
(55, 8, '60 cm²', 1),
(56, 8, '65 cm²', 0),
(57, 11, '60 reais', 0),
(58, 11, '62 reais', 0),
(59, 11, '64 reais', 1),
(60, 11, '68 reais', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `codigo_seguranca`
--

CREATE TABLE `codigo_seguranca` (
  `id` char(64) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `finalidade` varchar(24) NOT NULL,
  `codigo_hash` varchar(255) NOT NULL,
  `versao` int(11) NOT NULL DEFAULT 1,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `expira_em` datetime NOT NULL,
  `tentativas` int(11) NOT NULL DEFAULT 0,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `usado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `conteudo`
--

CREATE TABLE `conteudo` (
  `id_conteudo` int(11) NOT NULL,
  `id_materia` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `texto` mediumtext NOT NULL,
  `ordem` int(11) DEFAULT 1,
  `nivel_dificuldade` enum('facil','medio','avancado') DEFAULT 'medio'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `conteudo`
--

INSERT INTO `conteudo` (`id_conteudo`, `id_materia`, `titulo`, `texto`, `ordem`, `nivel_dificuldade`) VALUES
(1, 1, 'Área e Perímetro', 'Aprenda a calcular a área e o perímetro de diferentes figuras geométricas.', 1, 'facil'),
(2, 1, 'Frações', 'Aprenda a representar, comparar, somar, subtrair, multiplicar e dividir frações.', 2, 'facil'),
(3, 1, 'Números Decimais', 'Estude a representação, comparação e operações com números decimais.', 3, 'facil'),
(4, 1, 'Porcentagem', 'Aprenda a calcular porcentagens e aplicá-las em situações do dia a dia.', 4, 'medio'),
(5, 1, 'Razão e Proporção', 'Compreenda a relação entre grandezas utilizando razão e proporção.', 5, 'medio'),
(6, 1, 'Equações do 1º Grau', 'Aprenda a resolver equações do primeiro grau utilizando diferentes estratégias.', 6, 'medio'),
(7, 1, 'Expressões Algébricas', 'Aprenda a trabalhar com números, letras e operações em expressões algébricas.', 7, 'medio'),
(8, 1, 'Equações do 2º Grau', 'Estude como identificar e resolver equações do segundo grau.', 8, 'avancado'),
(9, 1, 'Teorema de Pitágoras', 'Aprenda a utilizar o Teorema de Pitágoras para calcular medidas em triângulos retângulos.', 9, 'avancado'),
(10, 1, 'Probabilidade', 'Aprenda a calcular a probabilidade de acontecimentos em diferentes situações.', 10, 'medio');

-- --------------------------------------------------------

--
-- Estrutura para tabela `exercicio`
--

CREATE TABLE `exercicio` (
  `id_exercicio` int(11) NOT NULL,
  `id_conteudo` int(11) NOT NULL,
  `pergunta` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `exercicio`
--

INSERT INTO `exercicio` (`id_exercicio`, `id_conteudo`, `pergunta`) VALUES
(1, 1, 'Calcule: 25 + 37 - 18.'),
(2, 1, 'Resolva: 8 × 7 + 12.'),
(3, 1, 'Qual é o resultado de 144 ÷ 12?'),
(4, 1, 'João tinha 50 reais e gastou 18 reais. Quanto dinheiro sobrou?'),
(5, 1, 'Calcule 3/4 + 1/4.'),
(6, 1, 'Qual é o valor de x na equação x + 15 = 32?'),
(7, 1, 'Um quadrado possui lado de 8 cm. Qual é o seu perímetro?'),
(8, 1, 'Calcule a área de um retângulo com 12 cm de comprimento e 5 cm de largura.'),
(9, 1, 'Resolva a equação 2x + 6 = 20.'),
(10, 1, 'Qual é a porcentagem de 25 em relação a 100?'),
(11, 1, 'Uma camiseta custa 80 reais e recebeu um desconto de 20%. Qual será o preço final?'),
(12, 1, 'Calcule: 2² + 3² + 4².'),
(13, 1, 'Um triângulo possui base de 10 cm e altura de 6 cm. Qual é sua área?'),
(14, 1, 'Se 5 cadernos custam 40 reais, quanto custam 8 cadernos pelo mesmo preço unitário?'),
(15, 1, 'Resolva: 3(x - 4) = 21.');

-- --------------------------------------------------------

--
-- Estrutura para tabela `imagem_exercicio`
--

CREATE TABLE `imagem_exercicio` (
  `id_imagem` int(11) NOT NULL,
  `id_exercicio` int(11) NOT NULL,
  `caminho_arquivo` varchar(255) NOT NULL,
  `legenda` varchar(255) DEFAULT NULL,
  `ordem` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `imagem_exercicio`
--

INSERT INTO `imagem_exercicio` (`id_imagem`, `id_exercicio`, `caminho_arquivo`, `legenda`, `ordem`) VALUES
(1, 1, 'imagens/exercicios/exercicio_01.jpg', 'Questão de cálculo: 25 + 37 - 18', 1),
(2, 2, 'imagens/exercicios/exercicio_02.jpg', 'Questão de cálculo: 8 x 7 + 12', 1),
(3, 3, 'imagens/exercicios/exercicio_03.jpg', 'Questão de divisão: 144 / 12', 1),
(4, 4, 'imagens/exercicios/exercicio_04.jpg', 'Questão de subtração envolvendo valores em reais', 1),
(5, 5, 'imagens/exercicios/exercicio_05.jpg', 'Questão envolvendo soma de frações: 3/4 + 1/4', 1),
(6, 6, 'imagens/exercicios/exercicio_06.jpg', 'Equação do primeiro grau: x + 15 = 32', 1),
(7, 7, 'imagens/exercicios/exercicio_07.jpg', 'Cálculo do perímetro de um quadrado com lado de 8 cm', 1),
(8, 8, 'imagens/exercicios/exercicio_08.jpg', 'Cálculo da área de um retângulo: 12 x 5', 1),
(9, 9, 'imagens/exercicios/exercicio_09.jpg', 'Equação do primeiro grau: 2x + 6 = 20', 1),
(10, 10, 'imagens/exercicios/exercicio_10.jpg', 'Questão sobre porcentagem: 25 em relação a 100', 1),
(11, 11, 'imagens/exercicios/exercicio_11.jpg', 'Questão sobre desconto de 20% em 80 reais', 1),
(12, 12, 'imagens/exercicios/exercicio_12.jpg', 'Cálculo envolvendo potências: 2^2 + 3^2 + 4^2', 1),
(13, 13, 'imagens/exercicios/exercicio_13.jpg', 'Cálculo da área de um triângulo com base 10 cm e altura 6 cm', 1),
(14, 14, 'imagens/exercicios/exercicio_14.jpg', 'Questão de razão e proporção envolvendo o preço de cadernos', 1),
(15, 15, 'imagens/exercicios/exercicio_15.jpg', 'Equação do primeiro grau: 3(x - 4) = 21', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `materia`
--

CREATE TABLE `materia` (
  `id_materia` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_publicacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `materia`
--

INSERT INTO `materia` (`id_materia`, `titulo`, `descricao`, `data_publicacao`) VALUES
(1, 'Matemática', 'Estudos relacionados à matemática, incluindo números operações, álgebra e geometria.', '2026-04-13 00:00:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `progresso_usuario`
--

CREATE TABLE `progresso_usuario` (
  `id_progresso` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_conteudo` int(11) NOT NULL,
  `concluido` tinyint(1) DEFAULT 0,
  `data_conclusao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `progresso_usuario`
--

INSERT INTO `progresso_usuario` (`id_progresso`, `id_usuario`, `id_conteudo`, `concluido`, `data_conclusao`) VALUES
(1, 1, 1, 1, '2026-08-10 14:30:00'),
(2, 1, 2, 1, '2026-08-10 15:00:00'),
(3, 1, 3, 0, NULL),
(4, 2, 1, 1, '2026-08-10 15:30:00'),
(5, 2, 2, 0, NULL),
(6, 2, 3, 1, '2026-08-10 16:30:00'),
(7, 3, 1, 0, NULL),
(8, 3, 2, 1, '2026-08-11 09:00:00'),
(9, 3, 3, 0, NULL),
(10, 5, 1, 1, '2026-08-11 10:00:00'),
(11, 5, 2, 1, '2026-08-11 10:30:00'),
(12, 5, 3, 0, NULL),
(13, 6, 1, 0, NULL),
(14, 6, 2, 1, '2026-08-11 11:00:00'),
(15, 6, 3, 1, '2026-08-11 11:30:00'),
(16, 8, 1, 1, '2026-08-12 13:00:00'),
(17, 8, 2, 0, NULL),
(18, 8, 3, 0, NULL),
(19, 9, 1, 1, '2026-08-12 14:00:00'),
(20, 9, 2, 1, '2026-08-12 14:30:00'),
(21, 9, 3, 0, NULL),
(22, 10, 1, 0, NULL),
(23, 10, 2, 0, NULL),
(24, 10, 3, 1, '2026-08-12 15:30:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `resumo`
--

CREATE TABLE `resumo` (
  `id_resumo` int(11) NOT NULL,
  `id_conteudo` int(11) NOT NULL,
  `caminho_imagem` varchar(255) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `resumo`
--

INSERT INTO `resumo` (`id_resumo`, `id_conteudo`, `caminho_imagem`, `descricao`) VALUES
(1, 1, 'imagens/area_perimetro.jpg', 'Resumo sobre como calcular área e perímetro de diferentes figuras geométricas.'),
(2, 2, 'imagens/fracoes.jpg', 'Resumo sobre representação, comparação, soma, subtração, multiplicação e divisão de frações.'),
(3, 3, 'imagens/numeros_decimais.jpg', 'Resumo sobre representação, comparação e operações com números decimais.'),
(4, 4, 'imagens/porcentagem.jpg', 'Resumo sobre cálculo de porcentagens e suas aplicações no dia a dia.'),
(5, 5, 'imagens/razao_proporcao.jpg', 'Resumo sobre razão e proporção e a relação entre diferentes grandezas.'),
(6, 6, 'imagens/equacao_1_grau.jpg', 'Resumo sobre como identificar e resolver equações do primeiro grau.'),
(7, 7, 'imagens/expressoes_algebricas.jpg', 'Resumo sobre expressões algébricas, números, letras e operações matemáticas.'),
(8, 8, 'imagens/equacao_2_grau.jpg', 'Resumo sobre identificação e resolução de equações do segundo grau.'),
(9, 9, 'imagens/teorema_pitagoras.jpg', 'Resumo sobre o Teorema de Pitágoras e seu uso para calcular medidas em triângulos retângulos.'),
(10, 10, 'imagens/probabilidade.jpg', 'Resumo sobre cálculo da probabilidade de ocorrência de determinados eventos.');

-- --------------------------------------------------------

--
-- Estrutura para tabela `solicitacao_professor`
--

CREATE TABLE `solicitacao_professor` (
  `id_solicitacao` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_administrador` int(11) DEFAULT NULL,
  `mensagem` text DEFAULT NULL,
  `status` enum('pendente','aprovada','recusada') NOT NULL DEFAULT 'pendente',
  `data_solicitacao` datetime DEFAULT current_timestamp(),
  `data_resposta` datetime DEFAULT NULL,
  `observacao_admin` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `solicitacao_professor`
--

INSERT INTO `solicitacao_professor` (`id_solicitacao`, `id_usuario`, `id_administrador`, `mensagem`, `status`, `data_solicitacao`, `data_resposta`, `observacao_admin`) VALUES
(1, 1, NULL, 'Gostaria de solicitar acesso como professor.', 'pendente', '2026-08-10 14:00:00', NULL, NULL),
(2, 2, 1, 'Tenho interesse em atuar como professor na plataforma.', 'aprovada', '2026-08-10 14:10:00', '2026-08-10 15:00:00', 'Solicitação aprovada pelo administrador.'),
(3, 3, 1, 'Gostaria de solicitar a alteração do meu perfil para professor.', 'recusada', '2026-08-10 14:20:00', '2026-08-10 16:00:00', 'Não foi possível aprovar a solicitação.'),
(4, 5, NULL, 'Gostaria de atuar como professor na plataforma.', 'pendente', '2026-08-11 09:00:00', NULL, NULL),
(5, 6, 1, 'Solicito acesso à área de professor.', 'aprovada', '2026-08-11 09:30:00', '2026-08-11 10:30:00', 'Solicitação aprovada.'),
(6, 8, NULL, 'Gostaria de me tornar professor da plataforma.', 'pendente', '2026-08-11 10:00:00', NULL, NULL),
(7, 9, 1, 'Solicito permissão para atuar como professor.', 'aprovada', '2026-08-12 13:00:00', '2026-08-12 14:00:00', 'Solicitação aprovada pelo administrador.'),
(8, 10, 1, 'Gostaria de solicitar acesso como professor.', 'recusada', '2026-08-12 14:30:00', '2026-08-12 15:30:00', 'Perfil não atende aos requisitos.');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `tipo_usuario` enum('aluno','professor','administrador') NOT NULL DEFAULT 'aluno',
  `ativo` tinyint(1) DEFAULT 1,
  `data_cadastro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nome`, `email`, `senha`, `foto_perfil`, `tipo_usuario`, `ativo`, `data_cadastro`) VALUES
(1, 'Jonathas Gabriel dos Santos', 'jonathasgabriel8@gmail.com', '1234567', NULL, 'administrador', 1, '2026-08-10 13:31:55'),
(2, 'José  Vinicios Balsaneli Corcovia', 'joseBalsaneli@gmail.com', '1234567', NULL, 'professor', 1, '2026-08-10 13:48:26'),
(3, 'Pricila Marques Candido', 'sicilia7963@uorak.com', '1234567', NULL, 'aluno', 1, '2026-04-08 11:09:34'),
(4, 'Gustavo Algusto Costa', 'gustavoalgustocosta@hotmail.com', '1234567', NULL, 'professor', 1, '2020-04-21 12:32:54'),
(5, 'Fernanda Leite Martinz', 'fernandaleit@gmail.com', '1234567', NULL, 'aluno', 1, '2021-03-21 08:12:23'),
(6, 'Lucas Alvarenga', 'lucasalvarenga@gmail.com', '1234567', NULL, 'professor', 1, '2020-02-05 14:19:03'),
(7, 'Julia Souza', 'juliasouza2@gmail.com', '1234567', NULL, 'professor', 1, '2019-05-22 06:17:34'),
(8, 'Maria Eduarda Silva Santos', 'mariaeduarda@gmail.com', '1234567', NULL, 'aluno', 1, '2026-08-10 13:50:18'),
(9, 'Lucas Gabriel Oliveira Souza', 'lucasgabriel@gmail.com', '1234567', NULL, 'professor', 1, '2026-08-10 13:50:18'),
(10, 'Ana Beatriz Pereira Costa', 'anabeatriz@gmail.com', '1234567', NULL, 'aluno', 1, '2026-08-10 13:50:18');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario_seguranca`
--

CREATE TABLE `usuario_seguranca` (
  `id_usuario` int(11) NOT NULL,
  `dois_fatores` tinyint(1) NOT NULL DEFAULT 0,
  `versao` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `video`
--

CREATE TABLE `video` (
  `id_video` int(11) NOT NULL,
  `id_conteudo` int(11) NOT NULL,
  `titulo` varchar(150) DEFAULT NULL,
  `url_video` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `video`
--

INSERT INTO `video` (`id_video`, `id_conteudo`, `titulo`, `url_video`) VALUES
(1, 1, 'Vídeo de apresentação do site', 'assets/videos/video-home.mp4'),
(2, 1, 'Área e Perímetro - Matemática', 'https://www.youtube.com/watch?v=exemplo1'),
(3, 2, 'Frações - Matemática', 'https://www.youtube.com/watch?v=exemplo2'),
(4, 3, 'Números Decimais - Matemática', 'https://www.youtube.com/watch?v=exemplo3'),
(5, 4, 'Porcentagem - Matemática', 'https://www.youtube.com/watch?v=exemplo4'),
(6, 5, 'Razão e Proporção - Matemática', 'https://www.youtube.com/watch?v=exemplo5'),
(7, 6, 'Equações do 1º Grau - Matemática', 'https://www.youtube.com/watch?v=exemplo6'),
(8, 7, 'Expressões Algébricas - Matemática', 'https://www.youtube.com/watch?v=exemplo7'),
(9, 8, 'Equações do 2º Grau - Matemática', 'https://www.youtube.com/watch?v=exemplo8'),
(10, 9, 'Teorema de Pitágoras - Matemática', 'https://www.youtube.com/watch?v=exemplo9'),
(11, 10, 'Probabilidade - Matemática', 'https://www.youtube.com/watch?v=exemplo10');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `alternativa`
--
ALTER TABLE `alternativa`
  ADD PRIMARY KEY (`id_alternativa`),
  ADD KEY `id_exercicio` (`id_exercicio`);

--
-- Índices de tabela `codigo_seguranca`
--
ALTER TABLE `codigo_seguranca`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_codigo_usuario` (`id_usuario`,`finalidade`,`criado_em`),
  ADD KEY `idx_codigo_validacao` (`id_usuario`,`finalidade`,`usado`,`expira_em`);

--
-- Índices de tabela `conteudo`
--
ALTER TABLE `conteudo`
  ADD PRIMARY KEY (`id_conteudo`),
  ADD KEY `id_materia` (`id_materia`);

--
-- Índices de tabela `exercicio`
--
ALTER TABLE `exercicio`
  ADD PRIMARY KEY (`id_exercicio`),
  ADD KEY `id_conteudo` (`id_conteudo`);

--
-- Índices de tabela `imagem_exercicio`
--
ALTER TABLE `imagem_exercicio`
  ADD PRIMARY KEY (`id_imagem`),
  ADD KEY `id_exercicio` (`id_exercicio`);

--
-- Índices de tabela `materia`
--
ALTER TABLE `materia`
  ADD PRIMARY KEY (`id_materia`);

--
-- Índices de tabela `progresso_usuario`
--
ALTER TABLE `progresso_usuario`
  ADD PRIMARY KEY (`id_progresso`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`,`id_conteudo`),
  ADD KEY `id_conteudo` (`id_conteudo`);

--
-- Índices de tabela `resumo`
--
ALTER TABLE `resumo`
  ADD PRIMARY KEY (`id_resumo`),
  ADD KEY `id_conteudo` (`id_conteudo`);

--
-- Índices de tabela `solicitacao_professor`
--
ALTER TABLE `solicitacao_professor`
  ADD PRIMARY KEY (`id_solicitacao`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_administrador` (`id_administrador`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `usuario_seguranca`
--
ALTER TABLE `usuario_seguranca`
  ADD PRIMARY KEY (`id_usuario`);

--
-- Índices de tabela `video`
--
ALTER TABLE `video`
  ADD PRIMARY KEY (`id_video`),
  ADD KEY `id_conteudo` (`id_conteudo`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `alternativa`
--
ALTER TABLE `alternativa`
  MODIFY `id_alternativa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT de tabela `conteudo`
--
ALTER TABLE `conteudo`
  MODIFY `id_conteudo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `exercicio`
--
ALTER TABLE `exercicio`
  MODIFY `id_exercicio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `imagem_exercicio`
--
ALTER TABLE `imagem_exercicio`
  MODIFY `id_imagem` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `materia`
--
ALTER TABLE `materia`
  MODIFY `id_materia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `progresso_usuario`
--
ALTER TABLE `progresso_usuario`
  MODIFY `id_progresso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de tabela `resumo`
--
ALTER TABLE `resumo`
  MODIFY `id_resumo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `solicitacao_professor`
--
ALTER TABLE `solicitacao_professor`
  MODIFY `id_solicitacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `video`
--
ALTER TABLE `video`
  MODIFY `id_video` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `alternativa`
--
ALTER TABLE `alternativa`
  ADD CONSTRAINT `alternativa_ibfk_1` FOREIGN KEY (`id_exercicio`) REFERENCES `exercicio` (`id_exercicio`) ON DELETE CASCADE;

--
-- Restrições para tabelas `codigo_seguranca`
--
ALTER TABLE `codigo_seguranca`
  ADD CONSTRAINT `fk_codigo_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Restrições para tabelas `conteudo`
--
ALTER TABLE `conteudo`
  ADD CONSTRAINT `conteudo_ibfk_1` FOREIGN KEY (`id_materia`) REFERENCES `materia` (`id_materia`) ON DELETE CASCADE;

--
-- Restrições para tabelas `exercicio`
--
ALTER TABLE `exercicio`
  ADD CONSTRAINT `exercicio_ibfk_1` FOREIGN KEY (`id_conteudo`) REFERENCES `conteudo` (`id_conteudo`) ON DELETE CASCADE;

--
-- Restrições para tabelas `imagem_exercicio`
--
ALTER TABLE `imagem_exercicio`
  ADD CONSTRAINT `imagem_exercicio_ibfk_1` FOREIGN KEY (`id_exercicio`) REFERENCES `exercicio` (`id_exercicio`) ON DELETE CASCADE;

--
-- Restrições para tabelas `progresso_usuario`
--
ALTER TABLE `progresso_usuario`
  ADD CONSTRAINT `progresso_usuario_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `progresso_usuario_ibfk_2` FOREIGN KEY (`id_conteudo`) REFERENCES `conteudo` (`id_conteudo`) ON DELETE CASCADE;

--
-- Restrições para tabelas `resumo`
--
ALTER TABLE `resumo`
  ADD CONSTRAINT `resumo_ibfk_1` FOREIGN KEY (`id_conteudo`) REFERENCES `conteudo` (`id_conteudo`) ON DELETE CASCADE;

--
-- Restrições para tabelas `solicitacao_professor`
--
ALTER TABLE `solicitacao_professor`
  ADD CONSTRAINT `solicitacao_professor_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `solicitacao_professor_ibfk_2` FOREIGN KEY (`id_administrador`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL;

--
-- Restrições para tabelas `usuario_seguranca`
--
ALTER TABLE `usuario_seguranca`
  ADD CONSTRAINT `fk_seguranca_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Restrições para tabelas `video`
--
ALTER TABLE `video`
  ADD CONSTRAINT `video_ibfk_1` FOREIGN KEY (`id_conteudo`) REFERENCES `conteudo` (`id_conteudo`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
