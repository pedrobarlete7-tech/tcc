<?php
declare(strict_types=1);

const SITE_SESSAO_DURACAO = 7 * 24 * 60 * 60;

function site_sessao_cookie(int $expira = 0): void
{
    $cookie = session_get_cookie_params();
    setcookie(session_name(), session_id(), [
        'expires' => $expira, 'path' => $cookie['path'],
        'domain' => $cookie['domain'], 'secure' => $cookie['secure'],
        'httponly' => true, 'samesite' => 'Lax',
    ]);
}

function site_sessao_iniciar(): void
{
    if (session_status() !== PHP_SESSION_NONE) return;
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    // Pasta privada, fora dos arquivos públicos e separada das sessões de outros sites.
    $pasta = rtrim(sys_get_temp_dir(), '/\\') . '/ensinotec-sessoes-' . substr(hash('sha256', __DIR__), 0, 24);
    $privada = is_dir($pasta) || @mkdir($pasta, 0700, true);
    $disponivel = false;
    if ($privada && is_writable($pasta)) {
        @ini_set('session.save_handler', 'files');
        @session_save_path($pasta);
        @ini_set('session.gc_maxlifetime', (string) SITE_SESSAO_DURACAO);
        @ini_set('session.gc_probability', '1');
        @ini_set('session.gc_divisor', '100');
        $disponivel = session_save_path() === $pasta
            && ini_get('session.save_handler') === 'files'
            && (int) ini_get('session.gc_maxlifetime') >= SITE_SESSAO_DURACAO;
    }
    $GLOBALS['site_sessao_persistente_disponivel'] = $disponivel;
    session_name('ENSINOTECSESSID');
    session_set_cookie_params([
        'lifetime' => 0, 'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax', 'path' => '/',
    ]);
    if (!session_start()) {
        http_response_code(503);
        exit('Não foi possível iniciar sua sessão. Tente novamente mais tarde.');
    }
    $expira = (int) ($_SESSION['manter_conectado_ate'] ?? 0);
    if ($expira && $expira <= time()) {
        $_SESSION = [];
        session_regenerate_id(true);
        site_sessao_cookie();
    }
}

function site_sessao_confirmar_login(): void
{
    if (array_key_exists('manter_conectado_pendente', $_SESSION)) {
        $manter = $_SESSION['manter_conectado_pendente'] === true
            && !empty($GLOBALS['site_sessao_persistente_disponivel']);
        unset($_SESSION['manter_conectado_pendente']);
        $_SESSION['manter_conectado_ate'] = $manter ? time() + SITE_SESSAO_DURACAO : 0;
    }
    site_sessao_cookie((int) ($_SESSION['manter_conectado_ate'] ?? 0));
}

site_sessao_iniciar();
