<?php
declare(strict_types=1);
// ALTERADO: corrige questões sem gravar respostas ou progresso no banco.
function responder_questao(PDO $pdo, int $usuario, int $conteudo, int $exercicio, $selecionadas): array
{
 if (!is_array($selecionadas) || !$selecionadas || count($selecionadas)>1000) throw new DomainException('Selecione pelo menos uma alternativa.');
 $ids=[];
 foreach($selecionadas as $valor) {
  $id=filter_var($valor,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
  if (!$id || in_array($id,$ids,true)) throw new DomainException('Alternativa inválida. Atualize a página.');
  $ids[]=$id;
 }
 sort($ids);
 $pdo->beginTransaction();
 try {
  $q=$pdo->prepare('SELECT ativo FROM usuario WHERE id_usuario=? FOR UPDATE');$q->execute([$usuario]);
  if ((int)$q->fetchColumn()!==1) throw new DomainException('Entre novamente para responder.');
  $q=$pdo->prepare('SELECT id_exercicio FROM exercicio WHERE id_exercicio=? AND id_conteudo=? FOR UPDATE');$q->execute([$exercicio,$conteudo]);
  if (!$q->fetchColumn()) throw new DomainException('Esta questão não pertence ao conteúdo.');
  $q=$pdo->prepare('SELECT id_alternativa,correta FROM alternativa WHERE id_exercicio=? ORDER BY id_alternativa FOR UPDATE');$q->execute([$exercicio]);$alternativas=$q->fetchAll();
  $validas=array_map('intval',array_column($alternativas,'id_alternativa'));
  $corretas=[];foreach($alternativas as $a) if((int)$a['correta']===1)$corretas[]=(int)$a['id_alternativa'];
  if (!$corretas) throw new DomainException('Esta questão ainda não tem gabarito disponível.');
  if (array_diff($ids,$validas)) throw new DomainException('Alternativa inválida. Atualize a página.');
  sort($corretas);$acertou=(int)($ids===$corretas);
  $pdo->commit();
  return ['selecionadas'=>$ids,'acertou'=>$acertou];
 } catch(Throwable $e) { if($pdo->inTransaction())$pdo->rollBack();throw $e; }
}
