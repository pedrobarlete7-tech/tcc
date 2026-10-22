<?php
declare(strict_types=1);

function senha_erro(string $senha): string
{
    if (!mb_check_encoding($senha, 'UTF-8') || mb_strlen($senha, 'UTF-8') < 8)
        return 'Use pelo menos 8 caracteres. Você pode usar uma frase como senha.';
    if (strlen($senha) > 72 || str_contains($senha, "\0"))
        return 'A senha ultrapassa o limite permitido ou contém um caractere inválido. Use até 72 bytes; acentos ocupam mais espaço.';
    return '';
}

// RFC 5321: local-part de até 64 octetos. O banco do site limita o total a 150.
function email_valido(string $email): bool
{
    if (strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
    $arroba = strrpos($email, '@');
    return $arroba !== false && $arroba <= 64 && strlen(substr($email, $arroba + 1)) <= 255;
}
