<?php
// ALTERADO: preserva o acesso enviado e remove o texto que impedia o menu de ler os dados.
$host = 'localhost';
$db   = 'tcc';
$user = 'tcc_user';
$pass = 'Tcc2026!';
$charset = 'utf8mb4';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log('[EnsinoTec] Falha na conexão. Código: ' . $e->getCode());
    http_response_code(503);
    exit('Não foi possível conectar ao banco de dados.');
}
