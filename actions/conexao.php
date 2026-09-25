<?php
declare(strict_types=1);

// ALTERADO: usa config/banco.php em vez do endereço do servidor antigo.
$caminhoConfiguracao = __DIR__ . '/../config/banco.php';
if (!is_file($caminhoConfiguracao)) {
    http_response_code(500);
    exit('Arquivo config/banco.php não encontrado. Confira a pasta onde foi salvo.');
}
$configBanco = require $caminhoConfiguracao;

try {
    // NOVO: confere a porta e os dados usados no endereço de conexão.
    $portaBanco = filter_var($configBanco['port'], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 65535],
    ]);
    if (
        $portaBanco === false ||
        $configBanco['host'] === '' ||
        $configBanco['name'] === '' ||
        preg_match('/[;\x00-\x1F]/', $configBanco['host'] . $configBanco['name'])
    ) {
        throw new RuntimeException('Configuração inválida.');
    }

    // ALTERADO: conexão PDO com suporte a acentos.
    $dsnBanco = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $configBanco['host'],
        $portaBanco,
        $configBanco['name']
    );

    // MANTIDO: as páginas do projeto usam a variável $pdo.
    $pdo = new PDO($dsnBanco, $configBanco['user'], $configBanco['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 5,
    ]);

    // ALTERADO: não imprime mensagem quando conecta com sucesso.
} catch (PDOException | RuntimeException $erroBanco) {
    // ALTERADO: não expõe credenciais ou detalhes internos ao visitante.
    error_log('[EnsinoTec] Falha na conexão. Código: ' . $erroBanco->getCode());
    http_response_code(503);
    exit('Não foi possível conectar ao banco. Confira se o MySQL está iniciado e se os dados em config/banco.php estão corretos.');
} finally {
    unset($configBanco, $dsnBanco, $portaBanco);
}