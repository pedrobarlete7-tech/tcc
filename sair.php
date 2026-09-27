<?php
// NOVO: encerra a sessão somente por uma solicitação válida do usuário.
require_once __DIR__ . '/includes/site/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Use a opção Sair no menu.');
}
if (!auth_csrf_valido()) {
    http_response_code(403);
    exit('Solicitação expirada. Atualize a página e tente novamente.');
}
$_SESSION = [];
$cookie = session_get_cookie_params();
setcookie(session_name(), '', [
    'expires' => time() - 3600, 'path' => $cookie['path'],
    'domain' => $cookie['domain'], 'secure' => $cookie['secure'],
    'httponly' => true, 'samesite' => 'Lax',
]);
session_destroy();
header('Location: login.php', true, 303);
exit;
