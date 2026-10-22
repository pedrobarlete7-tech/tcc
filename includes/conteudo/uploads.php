<?php

declare(strict_types=1);
// ALTERADO: identifica falhas de upload e de gravação na hospedagem.
function upload_bytes(string $value): int
{
    $value = trim($value);
    $number = (float)$value;
    return (int)($number * match (strtolower(substr($value, -1))) {
        'g' => 1073741824,
        'm' => 1048576,
        'k' => 1024,
        default => 1
    });
}
function upload_limite(string $tipo): int
{
    $limites = [$tipo === 'video' ? 100 * 1048576 : 10 * 1048576];
    foreach (['upload_max_filesize', 'post_max_size'] as $config) {
        $n = upload_bytes((string)ini_get($config));
        if ($n > 0) $limites[] = $config === 'post_max_size' ? max(1, $n - 65536) : $n;
    }
    return min($limites);
}
function upload_conteudo(array $arquivo, string $tipo): string
{
    if (!in_array($tipo, ['imagem', 'video'], true)) throw new DomainException('Tipo de arquivo inválido.');
    $error = $arquivo['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($error !== UPLOAD_ERR_OK) {
        $erros = [UPLOAD_ERR_INI_SIZE => 'O arquivo ultrapassa o limite de upload da hospedagem.', UPLOAD_ERR_FORM_SIZE => 'O arquivo ultrapassa o limite permitido.', UPLOAD_ERR_PARTIAL => 'O envio foi interrompido. Selecione o arquivo novamente.', UPLOAD_ERR_NO_FILE => 'Selecione um arquivo antes de enviar.', UPLOAD_ERR_NO_TMP_DIR => 'A hospedagem não possui uma pasta temporária de upload configurada.', UPLOAD_ERR_CANT_WRITE => 'A hospedagem não conseguiu gravar o arquivo temporário. Verifique as permissões e o espaço em disco.', UPLOAD_ERR_EXTENSION => 'Uma extensão da hospedagem bloqueou o envio do arquivo.'];
        throw new DomainException(is_int($error) ? ($erros[$error] ?? 'O envio não foi concluído.') : 'Envio de arquivo inválido.');
    }
    if (!class_exists('finfo')) throw new DomainException('A hospedagem precisa habilitar a extensão Fileinfo do PHP para validar o arquivo.');
    $tmp = $arquivo['tmp_name'] ?? '';
    if (!is_string($tmp) || !is_uploaded_file($tmp)) throw new DomainException('Arquivo de upload inválido.');
    $size = filesize($tmp);
    if (!$size || $size > upload_limite($tipo)) throw new DomainException('O arquivo está vazio ou ultrapassa o limite permitido.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    $tipos = $tipo === 'imagem' ? ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'] : ['video/mp4' => 'mp4', 'video/webm' => 'webm'];
    if (!isset($tipos[$mime])) throw new DomainException($tipo === 'imagem' ? 'Use uma imagem JPG, PNG, WebP ou GIF.' : 'Use um vídeo MP4 ou WebM.');
    if ($tipo === 'imagem') {
        $imagem = @getimagesize($tmp);
        if (!$imagem || ($imagem['mime'] ?? '') !== $mime || $imagem[0] * $imagem[1] > 40000000) throw new DomainException('Imagem inválida ou com dimensões muito grandes.');
    }
    $pasta = 'uploads/conteudos/' . ($tipo === 'imagem' ? 'imagens' : 'videos');
    $raiz = dirname(__DIR__, 2);
    $destino = $raiz . '/' . $pasta;
    if (!is_dir($destino) && !@mkdir($destino, 0755, true) && !is_dir($destino)) throw new DomainException('Não foi possível criar ' . $pasta . '. A hospedagem precisa permitir que o PHP crie pastas dentro de uploads/conteudos.');
    if (!is_writable($destino)) throw new DomainException('A pasta ' . $pasta . ' não permite gravação pelo PHP. Peça ao suporte da hospedagem para ajustar as permissões dessa pasta.');
    $caminho = $pasta . '/' . bin2hex(random_bytes(16)) . '.' . $tipos[$mime];
    if (!@move_uploaded_file($tmp, $raiz . '/' . $caminho)) throw new DomainException('O arquivo foi recebido, mas não pôde ser salvo em ' . $pasta . '. Confira a permissão da pasta e o espaço em disco.');
    return $caminho;
}

// Recebe também os arquivos enviados pelo formulário sem depender de JavaScript.
function upload_editor_arquivos(array &$conteudo, array $arquivos): void
{
    $obter = static function (string $campo, array $chaves) use ($arquivos) {
        $valor = $arquivos[$campo] ?? null;
        foreach ($chaves as $chave) {
            if (!is_array($valor) || !array_key_exists($chave, $valor)) return null;
            $valor = $valor[$chave];
        }
        return $valor;
    };
    $enviar = static function (array &$item, string $campo, array $chaves) use ($obter): void {
        if (!empty($item['remover'])) return;
        $erro = $obter('error', $chaves);
        if ($erro === null || $erro === UPLOAD_ERR_NO_FILE) return;
        $arquivo = [];
        foreach (['error', 'tmp_name', 'name', 'size', 'type'] as $parte) $arquivo[$parte] = $obter($parte, $chaves);
        $item[$campo] = upload_conteudo($arquivo, $campo === 'url_video' ? 'video' : 'imagem');
    };
    foreach (['resumos' => 'caminho_imagem', 'videos' => 'url_video'] as $tipo => $campo) {
        if (!is_array($conteudo[$tipo] ?? null)) continue;
        foreach ($conteudo[$tipo] as $i => &$item) if (is_array($item)) $enviar($item, $campo, [$tipo, $i, $campo]);
        unset($item);
    }
    if (!is_array($conteudo['questoes'] ?? null)) return;
    foreach ($conteudo['questoes'] as $q => &$questao) {
        if (!is_array($questao) || !empty($questao['remover']) || !is_array($questao['imagens'] ?? null)) continue;
        foreach ($questao['imagens'] as $i => &$item) if (is_array($item)) $enviar($item, 'caminho_arquivo', ['questoes', $q, 'imagens', $i, 'caminho_arquivo']);
        unset($item);
    }
    unset($questao);
}
