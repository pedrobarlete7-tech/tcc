<?php
declare(strict_types=1);
// NOVO: valida imagens e vídeos e salva os arquivos com nomes exclusivos.
function upload_bytes(string $value): int {
 $value=trim($value); $number=(float)$value;
 return (int)($number * match(strtolower(substr($value,-1))) {'g'=>1073741824,'m'=>1048576,'k'=>1024,default=>1});
}
function upload_limite(string $tipo): int {
 $limites=[$tipo==='video'?100*1048576:10*1048576];
 foreach (['upload_max_filesize','post_max_size'] as $config) {
  $n=upload_bytes((string)ini_get($config));
  if ($n>0) $limites[]=$config==='post_max_size'?max(1,$n-65536):$n;
 }
 return min($limites);
}
function upload_conteudo(array $arquivo, string $tipo): string {
 if (!in_array($tipo,['imagem','video'],true)) throw new DomainException('Tipo de arquivo inválido.');
 $error=$arquivo['error']??UPLOAD_ERR_NO_FILE;
 if ($error!==UPLOAD_ERR_OK) throw new DomainException(in_array($error,[UPLOAD_ERR_INI_SIZE,UPLOAD_ERR_FORM_SIZE],true)?'O arquivo ultrapassa o limite de upload do servidor.':'O envio não foi concluído. Selecione o arquivo novamente.');
 $tmp=$arquivo['tmp_name']??'';
 if (!is_string($tmp) || !is_uploaded_file($tmp)) throw new DomainException('Arquivo de upload inválido.');
 $size=filesize($tmp);
 if (!$size || $size>upload_limite($tipo)) throw new DomainException('O arquivo está vazio ou ultrapassa o limite permitido.');
 $mime=(new finfo(FILEINFO_MIME_TYPE))->file($tmp);
 $tipos=$tipo==='imagem'?['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif']:['video/mp4'=>'mp4','video/webm'=>'webm'];
 if (!isset($tipos[$mime])) throw new DomainException($tipo==='imagem'?'Use uma imagem JPG, PNG, WebP ou GIF.':'Use um vídeo MP4 ou WebM.');
 if ($tipo==='imagem') {
  $imagem=@getimagesize($tmp);
  if (!$imagem || ($imagem['mime']??'')!==$mime || $imagem[0]*$imagem[1]>40000000) throw new DomainException('Imagem inválida ou com dimensões muito grandes.');
 }
 $pasta='uploads/conteudos/'.($tipo==='imagem'?'imagens':'videos');
 $raiz=dirname(__DIR__,2); $destino=$raiz.'/'.$pasta;
 if (!is_dir($destino) && !@mkdir($destino,0755,true) && !is_dir($destino)) throw new RuntimeException('Não foi possível criar a pasta de uploads.');
 $caminho=$pasta.'/'.bin2hex(random_bytes(16)).'.'.$tipos[$mime];
 if (!move_uploaded_file($tmp,$raiz.'/'.$caminho)) throw new RuntimeException('Não foi possível guardar o arquivo na pasta de uploads.');
 return $caminho;
}
