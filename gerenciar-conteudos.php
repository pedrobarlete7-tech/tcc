<?php
declare(strict_types=1);
// ALTERADO: reúne todo o conteúdo em um formulário e salva os itens juntos, preservando seus IDs.
require_once __DIR__ . '/includes/site/auth.php';
require_once __DIR__ . '/includes/conteudo/dados.php';
header('Cache-Control: no-store');
if (!$autenticado) { header('Location: login.php', true, 303); exit; }
$permitido = in_array($contaAtual['tipo_usuario'] ?? '', ['professor', 'administrador'], true);
$tituloPagina = 'Conteúdos — EnsinoTec';
$estilosPagina = ['assets/css/gerenciar-materias.css', 'assets/css/editor-conteudo.css'];
$esquema = [
 'resumos' => ['resumo','id_resumo','id_conteudo','Imagem e resumo', ['descricao'=>['Resumo','textarea',255], 'caminho_imagem'=>['Link da imagem ou caminho do arquivo','media',255]]],
 'videos' => ['video','id_video','id_conteudo','Vídeo', ['titulo'=>['Título do vídeo','text',150], 'url_video'=>['Link do vídeo ou caminho do arquivo','media',255]]],
 'questoes' => ['exercicio','id_exercicio','id_conteudo','Questão', ['pergunta'=>['Enunciado','textarea',15000]]],
 'alternativas' => ['alternativa','id_alternativa','id_exercicio','Alternativa', ['texto'=>['Texto da alternativa','textarea',255], 'correta'=>['Resposta correta','checkbox',1]]],
 'imagens' => ['imagem_exercicio','id_imagem','id_exercicio','Imagem da questão', ['caminho_arquivo'=>['Link da imagem ou caminho do arquivo','media',255], 'legenda'=>['Legenda','text',255], 'ordem'=>['Ordem','number',100000]]],
];
function editor_id($v): int { return (int) (filter_var($v, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) ?: 0); }
function editor_texto($v): string { return is_string($v) ? trim($v) : ''; }
function editor_ler(PDO $pdo, int $id, array $esquema): array {
 $q=$pdo->prepare('SELECT * FROM conteudo WHERE id_conteudo=?'); $q->execute([$id]);
 $item=$q->fetch(); if (!$item) throw new DomainException('Conteúdo não encontrado.');
 foreach (['resumos','videos','questoes'] as $tipo) {
  [$t,$pk,$fk]=$esquema[$tipo];
  $q=$pdo->prepare("SELECT * FROM $t WHERE $fk=? ORDER BY $pk"); $q->execute([$id]);
  $item[$tipo]=$q->fetchAll();
 }
 foreach ($item['questoes'] as &$questao) {
  foreach (['alternativas','imagens'] as $tipo) {
   [$t,$pk,$fk]=$esquema[$tipo];
   $q=$pdo->prepare("SELECT * FROM $t WHERE $fk=? ORDER BY $pk"); $q->execute([$questao['id_exercicio']]);
   $questao[$tipo]=$q->fetchAll();
  }
 }
 unset($questao);
 return $item;
}
function editor_revisao(array $dados): string { return hash('sha256', serialize($dados)); }
function editor_lista($lista, string $tipo, array $atuais, array $esquema): array {
 if (!is_array($lista)) throw new DomainException('Formulário incompleto. Recarregue a página.');
 [$t,$pk,$fk,$rotulo,$campos]=$esquema[$tipo];
 $mapa=array_column($atuais,null,$pk); $vistos=[]; $saida=[];
 foreach ($lista as $linha) {
  if (!is_array($linha)) throw new DomainException('Item inválido.');
  $id=editor_id($linha[$pk]??null); $antigo=$mapa[$id]??[];
  if ($id && (!$antigo || isset($vistos[$id]))) throw new DomainException('Um item não pertence a este conteúdo ou está repetido.');
  $vistos[$id]=true;
  $remover=editor_texto($linha['remover']??'')==='1';
  if ($remover) { if ($id) $saida[] = [$pk=>$id,'remover'=>true]; continue; }
  $dados=[$pk=>$id]; $vazio=true;
  foreach ($campos as $nome=>[$label,$controle,$limite]) {
   $valor=editor_texto($linha[$nome]??'');
   if ($controle==='checkbox') $valor=$valor==='1' ? 1 : 0;
   if ($controle==='number') $valor=$valor==='' ? 1 : $valor;
   if (!in_array($controle,['checkbox','number'],true) && $valor!=='') $vazio=false;
   $dados[$nome]=$valor;
  }
  if ($tipo==='questoes') {
   foreach (['alternativas','imagens'] as $sub) {
    $dados[$sub]=editor_lista($linha[$sub]??[], $sub, $antigo[$sub]??[], $esquema);
    if ($dados[$sub]) $vazio=false;
   }
  }
  if ($vazio && !$id) continue;
  foreach ($campos as $nome=>[$label,$controle,$limite]) {
   $valor=$dados[$nome];
   if (in_array($controle,['text','textarea','media'],true) && mb_strlen((string)$valor)>$limite) throw new DomainException("$label: limite de $limite caracteres.");
   if ($controle==='number' && (!editor_id($valor) || (int)$valor>$limite)) throw new DomainException("$label: informe um número entre 1 e $limite.");
   if ($controle==='media' && $valor!=='' && $valor!==($antigo[$nome]??null) && url_midia($valor)===null) throw new DomainException("$label: use um link HTTP/HTTPS ou arquivo existente no site.");
  }
  $obrigatorio=['videos'=>'url_video','questoes'=>'pergunta','alternativas'=>'texto','imagens'=>'caminho_arquivo'][$tipo]??null;
  if ($obrigatorio && $dados[$obrigatorio]==='') throw new DomainException("Preencha os campos de $rotulo ou marque o item para remoção.");
  $saida[]=$dados;
 }
 return $saida;
}
function editor_gravar(PDO $pdo, int $pai, string $tipo, array $itens, array $esquema): void {
 [$t,$pk,$fk,$rotulo,$campos]=$esquema[$tipo];
 foreach ($itens as $item) {
  $id=$item[$pk];
  if (!empty($item['remover'])) {
   $q=$pdo->prepare("DELETE FROM $t WHERE $pk=? AND $fk=?"); $q->execute([$id,$pai]); continue;
  }
  $dados=array_intersect_key($item,$campos);
  if ($id) {
   $set=implode(', ',array_map(static fn($k)=>"$k=?",array_keys($dados)));
   $q=$pdo->prepare("UPDATE $t SET $set WHERE $pk=? AND $fk=?"); $q->execute([...array_values($dados),$id,$pai]);
  } else {
   $dados[$fk]=$pai; $colunas=implode(', ',array_keys($dados)); $marcas=implode(', ',array_fill(0,count($dados),'?'));
   $q=$pdo->prepare("INSERT INTO $t ($colunas) VALUES ($marcas)"); $q->execute(array_values($dados)); $id=(int)$pdo->lastInsertId();
  }
  if ($tipo==='questoes') foreach (['alternativas','imagens'] as $sub) editor_gravar($pdo,$id,$sub,$item[$sub],$esquema);
 }
}
function editor_campo(string $prefixo,string $nome,array $spec,$valor): void {
 $valor=is_scalar($valor)?$valor:'';
 [$label,$tipo,$max]=$spec; $id='campo-'.preg_replace('/[^a-zA-Z0-9_-]/','-',$prefixo.'-'.$nome);
 ?>
 <?php if ($tipo==='checkbox'): ?><label class="gestao-check"><input type="checkbox" name="<?= site_escape($prefixo.'['.$nome.']') ?>" value="1" <?= (string)$valor==='1'?'checked':'' ?>><?= site_escape($label) ?></label>
 <?php else: ?><label for="<?= site_escape($id) ?>"><?= site_escape($label) ?></label>
 <?php if ($tipo==='textarea'): ?><textarea id="<?= site_escape($id) ?>" name="<?= site_escape($prefixo.'['.$nome.']') ?>" maxlength="<?= $max ?>" rows="3"><?= site_escape((string)$valor) ?></textarea>
 <?php else: ?><input id="<?= site_escape($id) ?>" name="<?= site_escape($prefixo.'['.$nome.']') ?>" type="<?= $tipo==='number'?'number':'text' ?>" value="<?= site_escape((string)$valor) ?>" <?= $tipo==='number'?'min="1" max="'.$max.'"':'maxlength="'.$max.'"' ?>><?php endif; ?>
 <?php endif;
}
function editor_itens(string $tipo,string $prefixo,array $itens,array $esquema): void {
 ?>
 <div class="editor-colecao" data-collection="<?= $tipo ?>" data-name="<?= site_escape($prefixo) ?>"><div data-items>
 <?php foreach ($itens ?: [[]] as $i=>$item) if (is_array($item)) editor_item($tipo,$prefixo.'['.$i.']',$item,$esquema); ?>
 </div><button class="gestao-botao" type="button" data-add="<?= $tipo ?>">Adicionar mais: <?= site_escape($esquema[$tipo][3]) ?></button></div>
 <?php
}
function editor_item(string $tipo,string $prefixo,array $item,array $esquema): void {
 [$t,$pk,$fk,$rotulo,$campos]=$esquema[$tipo];
 ?>
 <fieldset class="editor-item"><legend><?= site_escape($rotulo) ?></legend>
 <input type="hidden" name="<?= site_escape($prefixo.'['.$pk.']') ?>" value="<?= (int)($item[$pk]??0) ?>">
 <?php foreach ($campos as $nome=>$spec) editor_campo($prefixo,$nome,$spec,$item[$nome]??($spec[1]==='number'?1:'')); ?>
 <?php if ($tipo==='questoes'): ?>
 <h3>Alternativas</h3><?php editor_itens('alternativas',$prefixo.'[alternativas]',is_array($item['alternativas']??null)?$item['alternativas']:[],$esquema); ?>
 <h3>Imagens da questão</h3><?php editor_itens('imagens',$prefixo.'[imagens]',is_array($item['imagens']??null)?$item['imagens']:[],$esquema); ?>
 <?php endif; ?>
 <label class="gestao-check editor-remover"><input type="checkbox" name="<?= site_escape($prefixo.'[remover]') ?>" value="1" <?= !empty($item['remover'])?'checked':'' ?>>Remover este item ao salvar</label>
 </fieldset>
 <?php
}
$erro=''; $aviso=$_SESSION['conteudos_aviso']??''; unset($_SESSION['conteudos_aviso']);
$dados=['titulo'=>'','texto'=>'','ordem'=>1,'nivel_dificuldade'=>'medio','resumos'=>[],'videos'=>[],'questoes'=>[]];
$materias=[]; $lista=[]; $pronto=false; $id=editor_id($_GET['editar']??$_GET['excluir']??null); $excluir=isset($_GET['excluir']);
$revisao=''; $pai=editor_id($_GET['id_materia']??null);
if (!$permitido) http_response_code(403);
else try {
 $materias=$pdo->query('SELECT id_materia,titulo FROM materia ORDER BY titulo,id_materia')->fetchAll();
 if ($id) { $dados=editor_ler($pdo,$id,$esquema); $pai=(int)$dados['id_materia']; $revisao=editor_revisao($dados); }
 if (!$pai) $pai=(int)($materias[0]['id_materia']??0);
 if (!in_array($pai,array_map('intval',array_column($materias,'id_materia')),true)) throw new DomainException('Cadastre ou selecione uma matéria existente.');
 $dados['id_materia']=$pai;
 $url='gerenciar-conteudos.php?id_materia='.$pai;
 $pronto=true;
 if ($_SERVER['REQUEST_METHOD']==='POST') {
  try {
   if (!auth_csrf_valido()) { http_response_code(403); throw new DomainException('O formulário expirou. Atualize a página.'); }
   if (editor_texto($_POST['completo']??'')!=='sim') throw new DomainException('O formulário chegou incompleto. Nenhuma alteração foi salva. Reduza a quantidade de itens por envio ou ajuste o limite de formulários do PHP.');
   $acao=editor_texto($_POST['acao']??'');
   if (!in_array($acao,['salvar','excluir'],true) || ($acao==='excluir' && (!$excluir || !$id || editor_texto($_POST['confirmar']??'')!=='sim')) || ($acao==='salvar' && $excluir)) throw new DomainException('Ação inválida ou exclusão não confirmada.');
   $enviado=is_array($_POST['conteudo']??null)?$_POST['conteudo']:[];
   $revisao=editor_texto($_POST['revisao']??'');
   $anterior=$dados;
   if ($acao==='salvar') $dados=array_merge($dados,$enviado);
   $pdo->beginTransaction();
   $q=$pdo->prepare('SELECT tipo_usuario,ativo FROM usuario WHERE id_usuario=? FOR UPDATE'); $q->execute([(int)$contaAtual['id_usuario']]); $autor=$q->fetch();
   if (!$autor || !(int)$autor['ativo'] || !in_array($autor['tipo_usuario'],['professor','administrador'],true)) throw new DomainException('Sua conta não pode alterar conteúdos.');
   $destino=$acao==='salvar'?editor_id($enviado['id_materia']??null):$pai;
   $q=$pdo->prepare('SELECT id_materia FROM materia WHERE id_materia IN (?,?) ORDER BY id_materia FOR UPDATE'); $q->execute([$pai,$destino]); $pais=array_map('intval',$q->fetchAll(PDO::FETCH_COLUMN));
   if (!in_array($pai,$pais,true) || !in_array($destino,$pais,true)) throw new DomainException('Selecione uma matéria válida.');
   if ($id) {
    $q=$pdo->prepare('SELECT id_conteudo FROM conteudo WHERE id_conteudo=? FOR UPDATE'); $q->execute([$id]); if (!$q->fetchColumn()) throw new DomainException('O conteúdo foi removido.');
    $anterior=editor_ler($pdo,$id,$esquema);
    if (!hash_equals(editor_revisao($anterior),editor_texto($_POST['revisao']??''))) throw new DomainException('Este conteúdo foi alterado em outra tela. Recarregue a página para ver a versão atual antes de salvar.');
   }
   if ($acao==='excluir') {
    $q=$pdo->prepare('DELETE FROM conteudo WHERE id_conteudo=?'); $q->execute([$id]);
   } else {
    $titulo=editor_texto($enviado['titulo']??''); $texto=editor_texto($enviado['texto']??''); $ordem=editor_id($enviado['ordem']??null); $nivel=editor_texto($enviado['nivel_dificuldade']??'');
    if ($titulo==='' || mb_strlen($titulo)>150 || $texto==='' || mb_strlen($texto)>100000) throw new DomainException('Preencha o título (até 150 caracteres) e a explicação (até 100.000 caracteres).');
    if (!$ordem || $ordem>100000 || !in_array($nivel,['facil','medio','avancado'],true)) throw new DomainException('Confira a ordem e a dificuldade.');
    $colecoes=[];
    foreach (['resumos','videos','questoes'] as $tipo) $colecoes[$tipo]=editor_lista($enviado[$tipo]??null,$tipo,$id?($anterior[$tipo]??[]):[],$esquema);
    if ($id) { $q=$pdo->prepare('UPDATE conteudo SET titulo=?,texto=?,id_materia=?,ordem=?,nivel_dificuldade=? WHERE id_conteudo=?'); $q->execute([$titulo,$texto,$destino,$ordem,$nivel,$id]); }
    else { $q=$pdo->prepare('INSERT INTO conteudo (titulo,texto,id_materia,ordem,nivel_dificuldade) VALUES (?,?,?,?,?)'); $q->execute([$titulo,$texto,$destino,$ordem,$nivel]); }
    $idSalvo=$id ?: (int)$pdo->lastInsertId();
    foreach ($colecoes as $tipo=>$itens) editor_gravar($pdo,$idSalvo,$tipo,$itens,$esquema);
   }
   $pdo->commit(); $_SESSION['conteudos_aviso']=$acao==='excluir'?'Conteúdo excluído.':'Conteúdo completo salvo.';
   header('Location: gerenciar-conteudos.php?id_materia='.$destino.($acao==='salvar'?'&editar='.$idSalvo:''),true,303); exit;
  } catch (DomainException $e) { if ($pdo->inTransaction()) $pdo->rollBack(); $erro=$e->getMessage(); }
  catch (PDOException $e) { if ($pdo->inTransaction()) $pdo->rollBack(); error_log('[EnsinoTec] Editor: '.$e->getCode()); http_response_code(503); $erro='Não foi possível salvar. Nenhuma alteração foi aplicada. Tente novamente.'; }
 }
 $q=$pdo->prepare('SELECT id_conteudo,titulo FROM conteudo WHERE id_materia=? ORDER BY ordem,id_conteudo'); $q->execute([$pai]); $lista=$q->fetchAll();
} catch (DomainException $e) { http_response_code(404); $erro=$e->getMessage(); }
catch (PDOException $e) { http_response_code(503); error_log('[EnsinoTec] Editor: '.$e->getCode()); $erro='Não foi possível carregar os conteúdos.'; }
require __DIR__.'/includes/site/header.php';
?>
<main class="gestao editor-pagina" id="conteudo-principal" tabindex="-1">
<?php if (!$permitido): ?><h1>Acesso restrito</h1><p>Somente professores e administradores podem gerenciar conteúdos.</p>
<?php else: ?>
<header class="gestao-intro"><p>EnsinoTec · Área de ensino</p><h1><?= $id?'Editar conteúdo':'Adicionar conteúdo' ?></h1><p>Texto, resumos, imagens, vídeos e questões no mesmo lugar.</p><div class="gestao-acoes"><a class="gestao-botao" href="gerenciar-materias.php">Voltar às matérias</a><?php if ($id): ?><a class="gestao-botao" href="conteudo.php?id_conteudo=<?= $id ?>">Ver página</a><?php endif; ?></div></header>
<?php if ($aviso): ?><p class="gestao-aviso" role="status"><?= site_escape($aviso) ?></p><?php endif; ?>
<?php if ($erro): ?><p class="gestao-aviso" role="alert"><?= site_escape($erro) ?></p><?php endif; ?>
<?php if ($pronto): ?>
<div class="editor-layout"><section class="gestao-card">
<?php if ($excluir): ?>
<h2>Excluir conteúdo</h2><p>Excluir <strong><?= site_escape($dados['titulo']) ?></strong> e todos os resumos, vídeos, imagens e questões vinculados? Esta ação não pode ser desfeita. Os arquivos no servidor serão mantidos.</p>
<form method="post" action="<?= site_escape($url.'&excluir='.$id) ?>"><input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>"><input type="hidden" name="revisao" value="<?= site_escape($revisao) ?>"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="completo" value="sim"><div class="gestao-acoes"><button class="gestao-botao principal" name="confirmar" value="sim">Sim, excluir</button><a class="gestao-botao" href="<?= site_escape($url.'&editar='.$id) ?>">Cancelar</a></div></form>
<?php else: ?>
<form method="post" id="editor-form" action="<?= site_escape($url.($id?'&editar='.$id:'')) ?>">
<input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>"><input type="hidden" name="revisao" value="<?= site_escape($revisao) ?>"><input type="hidden" name="acao" value="salvar">
<h2>Informações do conteúdo</h2>
<?php editor_campo('conteudo','titulo',['Título','text',150],editor_texto($dados['titulo']??'')); ?>
<label for="materia-editor">Matéria</label><select id="materia-editor" name="conteudo[id_materia]" required><?php foreach ($materias as $m): ?><option value="<?= (int)$m['id_materia'] ?>" <?= (int)$m['id_materia']===editor_id($dados['id_materia']??null)?'selected':'' ?>><?= site_escape($m['titulo']) ?></option><?php endforeach; ?></select>
<?php editor_campo('conteudo','ordem',['Ordem','number',100000],$dados['ordem']??1); ?>
<label for="nivel-editor">Dificuldade</label><select id="nivel-editor" name="conteudo[nivel_dificuldade]"><?php foreach (['facil'=>'Fácil','medio'=>'Médio','avancado'=>'Avançado'] as $k=>$v): ?><option value="<?= $k ?>" <?= ($dados['nivel_dificuldade']??'')===$k?'selected':'' ?>><?= $v ?></option><?php endforeach; ?></select>
<section class="editor-secao"><h2>Resumos e imagens</h2><p class="gestao-meta">Cada resumo aceita até 255 caracteres. A imagem é opcional.</p><?php editor_itens('resumos','conteudo[resumos]',is_array($dados['resumos']??null)?$dados['resumos']:[],$esquema); ?></section>
<section class="editor-secao"><h2>Explicação</h2><?php editor_campo('conteudo','texto',['Texto da explicação','textarea',100000],editor_texto($dados['texto']??'')); ?></section>
<section class="editor-secao"><h2>Vídeos</h2><p class="gestao-meta">Use links ou caminhos de arquivos já existentes no site.</p><?php editor_itens('videos','conteudo[videos]',is_array($dados['videos']??null)?$dados['videos']:[],$esquema); ?></section>
<section class="editor-secao"><h2>Questões</h2><?php editor_itens('questoes','conteudo[questoes]',is_array($dados['questoes']??null)?$dados['questoes']:[],$esquema); ?></section>
<p class="gestao-meta">Itens novos em branco são ignorados. Os itens marcados para remoção serão excluídos somente ao salvar.</p>
<input type="hidden" name="completo" value="sim">
<div class="gestao-acoes"><button class="gestao-botao principal" type="submit">Salvar conteúdo</button><a class="gestao-botao" href="<?= site_escape($url) ?>">Cancelar</a></div>
</form>
<?php foreach (array_keys($esquema) as $tipo): ?><template id="modelo-<?= $tipo ?>"><?php editor_item($tipo,'__PREFIX__',[],$esquema); ?></template><?php endforeach; ?>
<script src="assets/js/editor-conteudo.js" defer></script>
<?php endif; ?></section>
<aside class="gestao-card editor-lista"><h2>Conteúdos da matéria</h2><a class="gestao-botao" href="<?= site_escape($url) ?>">Novo conteúdo</a><?php foreach ($lista as $item): ?><article><h3><?= site_escape($item['titulo']) ?></h3><div class="gestao-acoes"><a class="gestao-botao" href="<?= site_escape($url.'&editar='.(int)$item['id_conteudo']) ?>">Editar</a><a class="gestao-botao" href="<?= site_escape($url.'&excluir='.(int)$item['id_conteudo']) ?>">Excluir</a></div></article><?php endforeach; ?></aside>
</div><?php endif; ?>
<?php endif; ?></main>
<?php require __DIR__.'/includes/site/footer.php'; ?>
