<?php
session_start();

require_once 'actions/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$cpf = trim($_POST['cpf'] ?? '');
$senha = trim($_POST['senha'] ?? '');

// Remove pontos e traço do CPF, caso o usuário digite formatado
$cpf = preg_replace('/\D/', '', $cpf);

if (empty($cpf) || empty($senha)) {
    $_SESSION['erro_login'] = 'Preencha CPF e senha.';
    header('Location: index.php');
    exit;
}

try {
    $sql = "SELECT id_usuario, cpf, nome, apelido, email, senha, tipo_usuario, ativo
            FROM usuario
            WHERE cpf = :cpf
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':cpf', $cpf);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $_SESSION['erro_login'] = 'CPF ou senha inválidos.';
        header('Location: index.php');
        exit;
    }

    if ((int)$usuario['ativo'] !== 1) {
        $_SESSION['erro_login'] = 'Usuário inativo.';
        header('Location: index.php');
        exit;
    }

    /*
      TEMPORÁRIO:
      Como sua tabela aparentemente ainda está com senha em texto simples,
      vamos comparar diretamente.
      Depois trocamos para password_hash() / password_verify().
    */
    if ($senha !== $usuario['senha']) {
        $_SESSION['erro_login'] = 'CPF ou senha inválidos.';
        header('Location: index.php');
        exit;
    }

    // Guarda dados do usuário na sessão
    $_SESSION['usuario_id'] = $usuario['id_usuario'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_apelido'] = $usuario['apelido'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_tipo'] = $usuario['tipo_usuario'];

    header('Location: painel.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['erro_login'] = 'Erro ao realizar login.';
    header('Location: index.php');
    exit;
}