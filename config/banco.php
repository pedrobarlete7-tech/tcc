<?php
declare(strict_types=1);

// NOVO: configuração padrão para testes no XAMPP.
// Altere os valores padrão se seu MySQL usar outra configuração.
// No servidor, as variáveis de ambiente têm prioridade.
// Este arquivo não carrega arquivos .env automaticamente.
return [
    'host' => getenv('DB_HOST') !== false ? getenv('DB_HOST') : '127.0.0.1',
    'port' => getenv('DB_PORT') !== false ? getenv('DB_PORT') : '3306',
    'name' => getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'tcc',
    'user' => getenv('DB_USER') !== false ? getenv('DB_USER') : 'root',
    'password' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
];