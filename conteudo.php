<?php
// ALTERADO: reproduz vídeos enviados por upload dentro da página.


require_once __DIR__ . '/includes/site/auth.php';
require_once __DIR__ . '/includes/conteudo/dados.php';


header('Cache-Control: no-store');
$idConteudo = filter_input(INPUT_GET, 'id_conteudo', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$conteudo = null;
$erroConteudo = '';
if (!$idConteudo) {
    require __DIR__ . '/404.php';
    exit;
} else {
    require_once __DIR__ . '/actions/conexao.php';
    try {
        $conteudo = carregar_conteudo($pdo, $idConteudo);
        if (!$conteudo) {
            require __DIR__ . '/404.php';
            exit;
        }
    } catch (PDOException $erro) {
        http_response_code(503);
        error_log('[EnsinoTec] Falha ao carregar conteúdo. Código: ' . $erro->getCode());
        $erroConteudo = 'Não foi possível carregar o conteúdo. Tente novamente mais tarde.';
    }
}

require_once __DIR__ . '/includes/conteudo/progresso.php';
$respostas=[]; $erroResposta=''; $respostasDisponiveis=true;
if ($_SERVER['REQUEST_METHOD']==='POST' && !$autenticado) { header('Location: login.php',true,303); exit; }
if ($conteudo && $autenticado) {
 if ($_SERVER['REQUEST_METHOD']==='POST') {
  try {
   if (!auth_csrf_valido()) { http_response_code(403); throw new DomainException('Solicitação expirada. Atualize a página e tente novamente.'); }
   $idQuestao=(int)(filter_var($_POST['exercicio']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])?:0);
   if (($_POST['acao']??'')!=='responder' || !$idQuestao) throw new DomainException('Questão inválida.');
   $respostas[$idQuestao]=responder_questao($pdo,(int)$contaAtual['id_usuario'],(int)$idConteudo,$idQuestao,$_POST['alternativas']??[]);
  } catch(DomainException $e) { $erroResposta=$e->getMessage(); }
  catch(PDOException $e) { http_response_code(503); error_log('[EnsinoTec] Resposta: '.$e->getCode()); $erroResposta='Não foi possível corrigir sua resposta. Tente novamente.'; }
 }
}
$tituloPagina = ($conteudo['titulo'] ?? 'Conteúdo') . ' — EnsinoTec';
$estilosPagina = ['assets/css/conteudo.css?v=progresso2'];
require __DIR__ . '/includes/site/header.php';
?>
<main class="conteudo" id="conteudo-principal" tabindex="-1">
<?php if ($erroConteudo !== ''): ?>
    <h1><span data-i18n="Conteúdo">Conteúdo</span></h1>
    <p role="alert"><span data-i18n-message><?= site_escape($erroConteudo) ?></span></p>
    <p><a href="index.php"><span data-i18n="Voltar ao início">Voltar ao início</span></a></p>
<?php else: ?>
    <h1><?= site_escape($conteudo['titulo']) ?></h1>
    <div class="resumo">
        <h3><?= site_escape($conteudo['materia_titulo']) ?> <span data-i18n="· Nível">· Nível</span> <?= site_escape($conteudo['nivel_dificuldade']) ?></h3>
        <?php foreach ($conteudo['resumos'] as $resumo): ?>
            <?php if (!empty($resumo['descricao'])): ?>
            <p><?= nl2br(site_escape($resumo['descricao'])) ?></p>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php foreach ($conteudo['resumos'] as $resumo): ?>
        <?php if (!empty($resumo['caminho_imagem'])): $urlImagem = url_midia($resumo['caminho_imagem']); ?>
        <div class="imagem">
            <?php if ($urlImagem !== null): ?>
            <img src="<?= site_escape($urlImagem) ?>" alt="<?= site_escape($resumo['descricao'] ?: $conteudo['titulo']) ?>" loading="lazy">
            <?php else: ?>
            <p class="midia-indisponivel"><span data-i18n="Imagem indisponível.">Imagem indisponível.</span></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
    <div class="texto-grande">
        <h2><span data-i18n="Explicação">Explicação</span></h2>
        <p><?= nl2br(site_escape($conteudo['texto'])) ?></p>
    </div>
    <?php if ($conteudo['videos']): ?>
    <div class="video">
        <h2><span data-i18n="Vídeo">Vídeo</span></h2>
        <?php foreach ($conteudo['videos'] as $video): $urlVideo = url_midia($video['url_video']); ?>
        <div class="caixa-video">
            <p><?= site_escape($video['titulo'] ?: 'Vídeo do conteúdo') ?></p>
            <?php if ($urlVideo !== null): ?>
            <?php if (str_starts_with($urlVideo,'uploads/conteudos/videos/')): ?>
            <video controls preload="metadata" style="width:100%;max-height:560px;border-radius:12px" src="<?= site_escape($urlVideo) ?>">Seu navegador não suporta este vídeo.</video>
            <?php endif; ?>
            <a href="<?= site_escape($urlVideo) ?>" target="_blank" rel="noopener noreferrer"><span data-i18n="Assistir vídeo">Assistir vídeo</span></a>
            <?php else: ?>
            <p class="midia-indisponivel"><span data-i18n="Vídeo indisponível.">Vídeo indisponível.</span></p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="exercicios">
        <?php if ($erroResposta): ?><p role="alert" data-i18n-message><?= site_escape($erroResposta) ?></p><?php endif; ?>
        <h2><span data-i18n="Exercícios">Exercícios</span></h2>
        <?php foreach ($conteudo['exercicios'] as $exercicio): ?>
        <div class="questao" id="questao-<?= (int)$exercicio['id_exercicio'] ?>">
            <p><strong><?= nl2br(site_escape($exercicio['pergunta'])) ?></strong></p>
            <?php foreach ($exercicio['imagens'] as $imagem): $urlImagem = url_midia($imagem['caminho_arquivo']); ?>
            <figure class="imagem-questao">
                <?php if ($urlImagem !== null): ?>
                <img src="<?= site_escape($urlImagem) ?>" alt="<?= site_escape($imagem['legenda'] ?: 'Imagem da questão') ?>" loading="lazy">
                <?php else: ?>
                <p class="midia-indisponivel"><span data-i18n="Imagem da questão indisponível.">Imagem da questão indisponível.</span></p>
                <?php endif; ?>
                <?php if (!empty($imagem['legenda'])): ?><figcaption><?= site_escape($imagem['legenda']) ?></figcaption><?php endif; ?>
            </figure>
            <?php endforeach; ?>
            <?php $resposta=$respostas[(int)$exercicio['id_exercicio']]??null; ?>
            <?php if ($autenticado && $respostasDisponiveis && $exercicio['alternativas'] && $exercicio['pode_responder']): ?>
            <form method="post" action="conteudo.php?id_conteudo=<?= (int)$idConteudo ?>#questao-<?= (int)$exercicio['id_exercicio'] ?>" class="questao-form">
                <input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>">
                <input type="hidden" name="acao" value="responder">
                <input type="hidden" name="exercicio" value="<?= (int)$exercicio['id_exercicio'] ?>">
                <fieldset><legend data-i18n="Selecione todas as alternativas corretas.">Selecione todas as alternativas corretas.</legend>
                <?php foreach ($exercicio['alternativas'] as $alternativa): ?>
                <label class="questao-alternativa"><input type="checkbox" name="alternativas[]" value="<?= (int)$alternativa['id_alternativa'] ?>" <?= in_array((int)$alternativa['id_alternativa'],$resposta['selecionadas']??[],true)?'checked':'' ?>><span><?= site_escape($alternativa['texto']) ?></span></label>
                <?php endforeach; ?>
                </fieldset>
                <button class="questao-enviar" type="submit" data-i18n-message><?= $resposta?'Responder novamente':'Confirmar resposta' ?></button>
            </form>
            <?php if ($resposta): ?><p class="questao-resultado" role="status" data-i18n-message><?= (int)$resposta['acertou']===1?'Você acertou!':'Você errou. Revise e tente novamente.' ?></p><?php endif; ?>
            <?php else: ?>
            <ul><?php foreach ($exercicio['alternativas'] as $alternativa): ?><li><?= site_escape($alternativa['texto']) ?></li><?php endforeach; ?></ul>
            <?php if (!$exercicio['pode_responder']): ?><p data-i18n="Ainda não há questões com gabarito disponível.">Ainda não há questões com gabarito disponível.</p><?php endif; ?>
            <?php if (!$autenticado): ?><a href="login.php" data-i18n="Entre para responder às questões.">Entre para responder às questões.</a><?php endif; ?>
            <?php endif; ?>
 </div>
        <?php endforeach; ?>
        <?php if (!$conteudo['exercicios']): ?>
        <p class="sem-exercicios"><span data-i18n="Nenhum exercício disponível para este conteúdo ainda.">Nenhum exercício disponível para este conteúdo ainda.</span></p>
        <?php endif; ?>
    </div>


<?php endif; ?>
</main>
<?php require __DIR__ . '/includes/site/footer.php'; ?>
