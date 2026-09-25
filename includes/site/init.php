<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function site_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// The authentication controller must populate this only after validating login.
$usuario = $_SESSION['usuario'] ?? null;
$autenticado = is_array($usuario) && !empty($usuario['id_usuario']);
$nomeUsuario = $autenticado ? (string) ($usuario['nome'] ?? 'Meu perfil') : '';
$fotoUsuario = $autenticado ? (string) ($usuario['foto_perfil'] ?? '') : '';
// Accept only local profile uploads, never arbitrary protocols or remote tracking URLs.
if (!preg_match('~^uploads/perfis/[a-zA-Z0-9_/-]+\.(?:png|jpe?g|webp)$~i', $fotoUsuario)) {
    $fotoUsuario = '';
}

// ALTERADO: as matérias agora são consultadas em materias.php pelo header.
