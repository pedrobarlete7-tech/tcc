<?php
declare(strict_types=1);

require_once __DIR__ . '/sessao.php';

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
            unset($_SESSION['usuario'], $_SESSION['auth_versao'], $_SESSION['manter_conectado_ate'], $_SESSION['manter_conectado_pendente']);
            session_regenerate_id(true);
            site_sessao_cookie();
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

