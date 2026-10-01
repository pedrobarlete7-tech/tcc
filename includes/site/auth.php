<?php

declare(strict_types=1);
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/seguranca.php';

// ALTERADO: aproveita uma única vez a confirmação feita na recuperação recente.
function auth_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function auth_csrf_valido(): bool
{
    $token = $_POST['csrf'] ?? null;
    return is_string($token) && hash_equals(auth_token(), $token);
}

function auth_cadastrar(PDO $pdo, string $nome, string $email, string $senha): void
{
    $consulta = $pdo->prepare('INSERT INTO usuario (nome, email, senha, tipo_usuario, ativo) VALUES (?, ?, ?, ?, ?)');
    $consulta->execute([$nome, $email, password_hash($senha, PASSWORD_BCRYPT), 'aluno', 1]);
}

function auth_entrar(PDO $pdo, string $email, string $senha): bool
{
    $consulta = $pdo->prepare('SELECT id_usuario, nome, email, senha, tipo_usuario, ativo FROM usuario WHERE email = ? LIMIT 1');
    $consulta->execute([$email]);
    $conta = $consulta->fetch(PDO::FETCH_ASSOC);
    $hash = $conta['senha'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
    $valida = password_verify($senha, $hash);
    if (!$conta || !$valida || (int) $conta['ativo'] !== 1) return false;
    $conta = seguranca_conta($pdo, (int) $conta['id_usuario']);
    if (!$conta) return false;
    $recuperado = $_SESSION['login_recuperado'] ?? [];
    unset($_SESSION['login_recuperado']);
    $confirmado = (int) ($recuperado['usuario'] ?? 0) === (int) $conta['id_usuario']
        && (int) ($recuperado['versao'] ?? 0) === (int) $conta['versao']
        && (int) ($recuperado['limite'] ?? 0) > time();
    if ($conta['dois_fatores'] && !$confirmado) {
        $codigo = seguranca_emitir($pdo, $conta, 'login');
        unset($_SESSION['usuario']);
        $_SESSION['desafio_login'] = $codigo;
        return true;
    }
    auth_concluir($conta);
    return true;
}

function auth_concluir(array $conta): void
{
    session_regenerate_id(true);
    $_SESSION['auth_versao'] = (int) $conta['versao'];
    unset($conta['senha'], $conta['ativo']);
    $_SESSION['usuario'] = $conta;
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    unset($_SESSION['login_falhas'], $_SESSION['login_bloqueado']);
    unset($_SESSION['desafio_login'], $_SESSION['login_recuperado']);
}
