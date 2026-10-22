<?php

declare(strict_types=1);
// ALTERADO: respeita os limites da hospedagem e identifica a etapa que impede salvar a foto.
function foto_perfil_bytes(string $valor): int
{
    $valor = trim($valor);
    if ($valor === '' || (float)$valor <= 0) return 0;
    $unidade = strtolower(substr($valor, -1));
    return (int)((float)$valor * (['k' => 1024, 'm' => 1048576, 'g' => 1073741824][$unidade] ?? 1));
}
function foto_perfil_limite(): int
{
    $limites = [5 * 1024 * 1024];
    $upload = foto_perfil_bytes((string)ini_get('upload_max_filesize'));
    $post = foto_perfil_bytes((string)ini_get('post_max_size'));
    if ($upload > 0) $limites[] = $upload;
    if ($post > 0) $limites[] = max(1, $post - 65536);
    return min($limites);
}
function foto_perfil_limite_texto(): string
{
    return number_format(foto_perfil_limite() / 1048576, 2, ',', '.') . ' MB';
}
function foto_perfil_post_excedido(): bool
{
    $limite = foto_perfil_bytes((string)ini_get('post_max_size'));
    return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $limite > 0 && (float)($_SERVER['CONTENT_LENGTH'] ?? 0) > $limite;
}
function foto_perfil_url(int $id): string
{
    if ($id < 1) return '';
    $pasta = 'uploads/perfis/' . $id;
    $meta = __DIR__ . '/../../' . $pasta . '/foto.json';
    if (!is_file($meta)) return '';
    $texto = @file_get_contents($meta);
    if ($texto === false) {
        error_log('[EnsinoTec] Foto: sem permissão para ler o índice da conta ' . $id);
        return '';
    }
    $nome = json_decode(preg_replace('/^\xEF\xBB\xBF/', '', $texto), true);
    if (!is_string($nome) || !preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/D', $nome)) return '';
    return is_file(__DIR__ . '/../../' . $pasta . '/' . $nome) ? $pasta . '/' . $nome : '';
}
function foto_perfil_salvar(int $id, array $arquivo): void
{
    if ($id < 1) throw new DomainException('Conta inválida.');
    if (!filter_var(ini_get('file_uploads'), FILTER_VALIDATE_BOOLEAN)) throw new DomainException('O envio de arquivos está desativado na hospedagem. Peça ao suporte para ativar file_uploads.');
    $codigo = $arquivo['error'] ?? UPLOAD_ERR_NO_FILE;
    if (!is_int($codigo)) throw new DomainException('Envio de imagem inválido.');
    $erros = [
        UPLOAD_ERR_INI_SIZE => 'A foto ultrapassa o limite de upload da hospedagem. Envie uma foto de até ' . foto_perfil_limite_texto() . '.',
        UPLOAD_ERR_FORM_SIZE => 'A foto ultrapassa o limite permitido. Envie uma foto de até ' . foto_perfil_limite_texto() . '.',
        UPLOAD_ERR_PARTIAL => 'O envio da foto foi interrompido. Selecione a imagem e tente novamente.',
        UPLOAD_ERR_NO_FILE => 'Selecione uma foto antes de salvar.',
        UPLOAD_ERR_NO_TMP_DIR => 'A hospedagem está sem pasta temporária para receber arquivos. Peça ao suporte para verificar upload_tmp_dir.',
        UPLOAD_ERR_CANT_WRITE => 'A hospedagem não conseguiu gravar o arquivo temporário. Peça ao suporte para verificar o espaço em disco e as permissões da pasta temporária.',
        UPLOAD_ERR_EXTENSION => 'Uma extensão da hospedagem bloqueou o envio da foto. Consulte o suporte da hospedagem.'
    ];
    if ($codigo !== UPLOAD_ERR_OK) throw new DomainException($erros[$codigo] ?? 'Não foi possível receber a foto. Tente novamente.');
    $tmp = $arquivo['tmp_name'] ?? null;
    if (!is_string($tmp) || !is_uploaded_file($tmp)) throw new DomainException('Envio de imagem inválido.');
    $tamanho = filesize($tmp);
    if (!$tamanho || $tamanho > foto_perfil_limite()) throw new DomainException('A foto deve ter até ' . foto_perfil_limite_texto() . '.');
    if (!class_exists('finfo')) throw new DomainException('A extensão Fileinfo do PHP precisa estar ativa na hospedagem para validar as imagens.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    $tipos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $imagem = @getimagesize($tmp);
    if (!isset($tipos[$mime]) || !$imagem || ($imagem['mime'] ?? '') !== $mime || $imagem[0] * $imagem[1] > 25000000) throw new DomainException('Escolha uma imagem JPG, PNG ou WebP válida, de até 25 megapixels.');
    $pasta = __DIR__ . '/../../uploads/perfis/' . $id;
    if (!is_dir($pasta) && !@mkdir($pasta, 0755, true) && !is_dir($pasta)) throw new DomainException('Não foi possível criar uploads/perfis/' . $id . '. A hospedagem precisa permitir que o PHP crie pastas dentro de uploads/perfis.');
    if (!is_writable($pasta)) throw new DomainException('A pasta uploads/perfis/' . $id . ' não permite gravação pelo PHP. Peça ao suporte da hospedagem para ajustar o proprietário e a permissão dessa pasta.');
    $nome = bin2hex(random_bytes(16)) . '.' . $tipos[$mime];
    if (!@move_uploaded_file($tmp, $pasta . '/' . $nome)) throw new DomainException('A foto foi recebida, mas não pôde ser gravada em uploads/perfis/' . $id . '. Confira o espaço em disco e a permissão de gravação dessa pasta.');
    $meta = @tempnam($pasta, 'foto-');
    try {
        if (!$meta || realpath(dirname($meta)) !== realpath($pasta) || @file_put_contents($meta, json_encode($nome)) !== strlen(json_encode($nome)) || !@rename($meta, $pasta . '/foto.json')) throw new DomainException('Não foi possível atualizar uploads/perfis/' . $id . '/foto.json. Peça ao suporte para verificar a permissão de gravação e substituição de arquivos nessa pasta.');
    } catch (Throwable $e) {
        @unlink($pasta . '/' . $nome);
        throw $e;
    } finally {
        if ($meta && is_file($meta)) @unlink($meta);
    }
}
