<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

function site_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ALTERADO: carrega a foto atual da conta também para a navbar.
if (!empty($_SESSION['usuario']['id_usuario'])) {
    require_once __DIR__ . '/../../actions/conexao.php';
    require_once __DIR__ . '/seguranca.php';
    try {
        $contaAtual = seguranca_conta($pdo, (int) $_SESSION['usuario']['id_usuario']);
        if (!$contaAtual || (int) ($_SESSION['auth_versao'] ?? 0) !== (int) $contaAtual['versao']) {
            unset($_SESSION['usuario'], $_SESSION['auth_versao']);
            session_regenerate_id(true);
        }
    } catch (PDOException $e) {
        http_response_code(503);
        exit('Não foi possível verificar a conta. Confira se o SQL de segurança foi importado.');
    }
}
$usuario = $_SESSION['usuario'] ?? null;
$autenticado = is_array($usuario) && !empty($usuario['id_usuario']);
$nomeUsuario = $autenticado ? (string) ($usuario['nome'] ?? 'Meu perfil') : '';
require_once __DIR__ . '/foto-perfil.php';
$fotoUsuario = $autenticado ? foto_perfil_url((int)$usuario['id_usuario']) : '';
if (!preg_match('~^uploads/perfis/[a-zA-Z0-9_/-]+\.(?:png|jpe?g|webp)$~i', $fotoUsuario)) {
    $fotoUsuario = '';
}

