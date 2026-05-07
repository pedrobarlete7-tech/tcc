<?php
// ===============================
// ARQUIVO DE CONEXÃO COM O BANCO
// Utilizando PHP + PDO
// ===============================

// Dados da conexão (preencher em aula)
$host = '10.68.103.57';     // Ex: localhost
$db   = 'tcc';     // Nome do banco de dados
$user = 'tcc_user';     // Usuário do banco
$pass = 'tcc2026';     // Senha do banco

// Charset (padrão recomendado)
$charset = 'utf8mb4';

try {
    // Monta a string de conexão (DSN)
    // DSN = Data Source Name (informações para conectar no banco)
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    // Opções do PDO (boas práticas)
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Mostra erros como exceção
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Retorna dados como array associativo
        PDO::ATTR_EMULATE_PREPARES => false, // Usa prepared statements reais (mais seguro)
    ];

    // Cria a conexão com o banco
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Mensagem opcional de sucesso (pode comentar depois)
    echo "Conexão realizada com sucesso! Agora vai!";

} catch (PDOException $e) {
    // Caso ocorra erro na conexão
    die("Erro na conexão: " . $e->getMessage());
}
?>