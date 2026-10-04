<?php
declare(strict_types=1);
// NOVO: recebe mídia de professores e administradores e devolve o caminho salvo.
require_once __DIR__.'/../includes/site/auth.php';
require_once __DIR__.'/../includes/conteudo/uploads.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
try {
 if ($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(405); header('Allow: POST'); throw new DomainException('Envie um arquivo pelo formulário.'); }
 if (!$autenticado || !in_array($contaAtual['tipo_usuario']??'',['professor','administrador'],true)) { http_response_code(403); throw new DomainException('Entre como professor ou administrador para enviar arquivos.'); }
 $limitePost=upload_bytes((string)ini_get('post_max_size'));
 if ($limitePost>0 && (int)($_SERVER['CONTENT_LENGTH']??0)>$limitePost) { http_response_code(413); throw new DomainException('O arquivo ultrapassa o limite de envio configurado no PHP.'); }
 if (!auth_csrf_valido()) { http_response_code(403); throw new DomainException('Sua sessão expirou. Atualize a página antes de enviar.'); }
 $tipo=is_string($_POST['tipo']??null)?$_POST['tipo']:'';
 $arquivo=$_FILES['arquivo']??[];
 if (!is_array($arquivo) || is_array($arquivo['error']??null)) throw new DomainException('Selecione apenas um arquivo por campo.');
 $caminho=upload_conteudo($arquivo,$tipo);
 echo json_encode(['ok'=>true,'caminho'=>$caminho],JSON_UNESCAPED_UNICODE);
} catch (DomainException $e) {
 if (http_response_code()<400) http_response_code(422);
 echo json_encode(['ok'=>false,'erro'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
 http_response_code(500); error_log('[EnsinoTec] Falha ao receber mídia: '.$e->getMessage());
 echo json_encode(['ok'=>false,'erro'=>'Não foi possível guardar o arquivo. Confira a permissão da pasta uploads.']);
}
