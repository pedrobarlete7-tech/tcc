<?php
// Arquivo de conexão com o banco de dados

$host = '2.25.135.118';
$db   = 'tcc';
$user = 'tcc_user';
$pass = 'Tcc2026!';
$charset = 'utf8mb4';

try {
    $dsn = "mysql:host=$host;dbname=$db; charset=$charset";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Exibe erros
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retorna array associativo
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Segurança (prepared real)
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);

    echo "Conectado com sucesso!"; // (opcional para teste)

} catch (PDOException $e) {
    die('Erro na conexão: ' . $e->getMessage());
}
?>