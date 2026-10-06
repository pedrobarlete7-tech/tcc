<?php

declare(strict_types=1);
// ALTERADO: salva a classificação dos conteúdos em JSON, sem novas tabelas.
require_once __DIR__ . '/includes/site/auth.php';
require_once __DIR__ . '/includes/conteudo/dados.php';
require_once __DIR__ . '/includes/conteudo/uploads.php';
require_once __DIR__ . '/includes/conteudo/subdivisoes.php';
header('Cache-Control: no-store');
$automatico = $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['automatico'] ?? '') === '1';
function editor_json(array $resultado, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
if (!$autenticado) {
    if ($automatico) editor_json(['ok' => false, 'erro' => 'Sua sessão terminou. Entre novamente antes de salvar.'], 401);
    header('Location: login.php', true, 303);
    exit;
}
$permitido = in_array($contaAtual['tipo_usuario'] ?? '', ['professor', 'administrador'], true);
$pedido = is_string($_POST['pedido'] ?? null) ? $_POST['pedido'] : '';
if ($automatico) {
    if (!$permitido || !auth_csrf_valido()) editor_json(['ok' => false, 'erro' => 'Sua sessão expirou ou você não tem permissão para salvar.'], 403);
    if (!preg_match('/^[a-f0-9]{32}$/D', $pedido)) editor_json(['ok' => false, 'erro' => 'Solicitação inválida. Atualize a página.'], 400);
    if (isset($_SESSION['editor_pedidos'][$pedido])) {
        $anteriorPedido = $_SESSION['editor_pedidos'][$pedido];
        if (!hash_equals($anteriorPedido['hash'], hash('sha256', serialize($_POST)))) editor_json(['ok' => false, 'erro' => 'Solicitação repetida com dados diferentes.'], 409);
        editor_json($anteriorPedido['resultado']);
    }
}
$tituloPagina = 'Conteúdos — EnsinoTec';
$estilosPagina = ['assets/css/gerenciar-materias.css', 'assets/css/editor-conteudo.css'];
$esquema = [
    'resumos' => ['resumo', 'id_resumo', 'id_conteudo', 'Imagem e resumo', ['descricao' => ['Resumo', 'textarea', 255], 'caminho_imagem' => ['Enviar imagem', 'media', 255]]],
    'videos' => ['video', 'id_video', 'id_conteudo', 'Vídeo', ['titulo' => ['Título do vídeo', 'text', 150], 'url_video' => ['Enviar vídeo', 'media', 255]]],
    'questoes' => ['exercicio', 'id_exercicio', 'id_conteudo', 'Questão', ['pergunta' => ['Enunciado', 'textarea', 15000]]],
    'alternativas' => ['alternativa', 'id_alternativa', 'id_exercicio', 'Alternativa', ['texto' => ['Texto da alternativa', 'textarea', 255], 'correta' => ['Resposta correta', 'checkbox', 1]]],
    'imagens' => ['imagem_exercicio', 'id_imagem', 'id_exercicio', 'Imagem da questão', ['caminho_arquivo' => ['Enviar imagem', 'media', 255], 'legenda' => ['Legenda', 'text', 255], 'ordem' => ['Ordem', 'number', 100000]]],
];
function editor_id($v): int
{
    return (int) (filter_var($v, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0);
}
function editor_texto($v): string
{
    return is_string($v) ? trim($v) : '';
}
function editor_ler(PDO $pdo, int $id, array $esquema): array
{
    $q = $pdo->prepare('SELECT * FROM conteudo WHERE id_conteudo=?');
    $q->execute([$id]);
    $item = $q->fetch();
    if (!$item) throw new DomainException('Conteúdo não encontrado.');
    $item['id_subdivisao'] = $GLOBALS['arquivoSubdivisoes']->grupo($id, (int)$item['id_materia'])['id_subdivisao'] ?? null;
    foreach (['resumos', 'videos', 'questoes'] as $tipo) {
        [$t, $pk, $fk] = $esquema[$tipo];
        $q = $pdo->prepare("SELECT * FROM $t WHERE $fk=? ORDER BY $pk");
        $q->execute([$id]);
        $item[$tipo] = $q->fetchAll();
    }
    foreach ($item['questoes'] as &$questao) {
        foreach (['alternativas', 'imagens'] as $tipo) {
            [$t, $pk, $fk] = $esquema[$tipo];
            $q = $pdo->prepare("SELECT * FROM $t WHERE $fk=? ORDER BY $pk");
            $q->execute([$questao['id_exercicio']]);
            $questao[$tipo] = $q->fetchAll();
        }
    }
    unset($questao);
    return $item;
}
function editor_revisao(array $dados): string
{
    return hash('sha256', serialize($dados));
}
function editor_lista($lista, string $tipo, array $atuais, array $esquema): array
{
    if (!is_array($lista)) throw new DomainException('Formulário incompleto. Recarregue a página.');
    [$t, $pk, $fk, $rotulo, $campos] = $esquema[$tipo];
    $mapa = array_column($atuais, null, $pk);
    $vistos = [];
    $saida = [];
    foreach ($lista as $chave => $linha) {
        if (!preg_match('/^[0-9]+$/D', (string)$chave)) throw new DomainException('Índice de item inválido.');
        if (!is_array($linha)) throw new DomainException('Item inválido.');
        $id = editor_id($linha[$pk] ?? null);
        $antigo = $mapa[$id] ?? [];
        if ($id && (!$antigo || isset($vistos[$id]))) throw new DomainException('Um item não pertence a este conteúdo ou está repetido.');
        $vistos[$id] = true;
        $remover = editor_texto($linha['remover'] ?? '') === '1';
        if ($remover) {
            $saida[] = [$pk => $id, 'remover' => true, '_chave' => $chave];
            continue;
        }
        $dados = [$pk => $id, '_chave' => $chave];
        $vazio = true;
        foreach ($campos as $nome => [$label, $controle, $limite]) {
            $valor = editor_texto($linha[$nome] ?? '');
            if ($controle === 'checkbox') $valor = $valor === '1' ? 1 : 0;
            if ($controle === 'number') $valor = $valor === '' ? 1 : $valor;
            if (!in_array($controle, ['checkbox', 'number'], true) && $valor !== '') $vazio = false;
            $dados[$nome] = $valor;
        }
        if ($tipo === 'questoes') {
            foreach (['alternativas', 'imagens'] as $sub) {
                $dados[$sub] = editor_lista($linha[$sub] ?? [], $sub, $antigo[$sub] ?? [], $esquema);
                if ($dados[$sub]) $vazio = false;
            }
        }
        if ($vazio && !$id) continue;
        foreach ($campos as $nome => [$label, $controle, $limite]) {
            $valor = $dados[$nome];
            if (in_array($controle, ['text', 'textarea', 'media'], true) && mb_strlen((string)$valor) > $limite) throw new DomainException("$label: limite de $limite caracteres.");
            if ($controle === 'number' && (!editor_id($valor) || (int)$valor > $limite)) throw new DomainException("$label: informe um número entre 1 e $limite.");
            if ($controle === 'media' && $valor !== '' && $valor !== ($antigo[$nome] ?? null) && url_midia($valor) === null) throw new DomainException("$label: use um link HTTP/HTTPS ou arquivo existente no site.");
        }
        $obrigatorio = ['videos' => 'url_video', 'questoes' => 'pergunta', 'alternativas' => 'texto', 'imagens' => 'caminho_arquivo'][$tipo] ?? null;
        if ($obrigatorio && $dados[$obrigatorio] === '') throw new DomainException("Preencha os campos de $rotulo ou marque o item para remoção.");
        $saida[] = $dados;
    }
    return $saida;
}
function editor_gravar(PDO $pdo, int $pai, string $tipo, array $itens, array $esquema, string $prefixo, array &$ids, array &$removidos): void
{
    [$t, $pk, $fk, $rotulo, $campos] = $esquema[$tipo];
    foreach ($itens as $item) {
        $nome = $prefixo . '[' . $item['_chave'] . ']';
        $id = $item[$pk];
        if (!empty($item['remover'])) {
            if ($id) {
                $q = $pdo->prepare("DELETE FROM $t WHERE $pk=? AND $fk=?");
                $q->execute([$id, $pai]);
            }
            $removidos[] = $nome . '[' . $pk . ']';
            continue;
        }
        $dados = array_intersect_key($item, $campos);
        if ($id) {
            $set = implode(', ', array_map(static fn($k) => "$k=?", array_keys($dados)));
            $q = $pdo->prepare("UPDATE $t SET $set WHERE $pk=? AND $fk=?");
            $q->execute([...array_values($dados), $id, $pai]);
        } else {
            $dados[$fk] = $pai;
            $colunas = implode(', ', array_keys($dados));
            $marcas = implode(', ', array_fill(0, count($dados), '?'));
            $q = $pdo->prepare("INSERT INTO $t ($colunas) VALUES ($marcas)");
            $q->execute(array_values($dados));
            $id = (int)$pdo->lastInsertId();
        }
        $ids[$nome . '[' . $pk . ']'] = $id;
        if ($tipo === 'questoes') foreach (['alternativas', 'imagens'] as $sub) editor_gravar($pdo, $id, $sub, $item[$sub], $esquema, $nome . '[' . $sub . ']', $ids, $removidos);
    }
}
function editor_campo(string $prefixo, string $nome, array $spec, $valor): void
{
    $valor = is_scalar($valor) ? $valor : '';
    [$label, $tipo, $max] = $spec;
    $id = 'campo-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', $prefixo . '-' . $nome);
?>
    <?php if ($tipo === 'checkbox'): ?><label class="gestao-check"><input type="checkbox" name="<?= site_escape($prefixo . '[' . $nome . ']') ?>" value="1" <?= (string)$valor === '1' ? 'checked' : '' ?>><?= site_escape($label) ?></label>
    <?php else: ?><label for="<?= site_escape($id) ?>"><?= site_escape($label) ?></label>
        <?php if ($tipo === 'media'): $midiaTipo = $nome === 'url_video' ? 'video' : 'imagem';
            $urlAtual = url_midia((string)$valor); ?>
            <div class="editor-upload" data-tipo="<?= $midiaTipo ?>" data-limite="<?= upload_limite($midiaTipo) ?>">
                <input type="hidden" name="<?= site_escape($prefixo . '[' . $nome . ']') ?>" value="<?= site_escape((string)$valor) ?>" data-upload-caminho>
                <input type="file" id="<?= site_escape($id) ?>" accept="<?= $midiaTipo === 'video' ? 'video/mp4,video/webm' : 'image/jpeg,image/png,image/webp,image/gif' ?>" data-upload-arquivo>
                <small><?= $midiaTipo === 'video' ? 'MP4 ou WebM' : 'JPG, PNG, WebP ou GIF' ?> · Até <?= number_format(upload_limite($midiaTipo) / 1048576, 1, ',', '.') ?> MB por arquivo.</small>
                <p data-upload-status role="status"><?= $valor !== '' ? 'Arquivo já vinculado. Selecione outro para substituir.' : 'Selecione um arquivo do seu dispositivo.' ?></p>
                <a data-upload-preview <?= !$urlAtual ? 'hidden' : '' ?> href="<?= site_escape($urlAtual ?? '#') ?>" target="_blank" rel="noopener noreferrer">Abrir arquivo atual ↗</a>
                <button type="button" class="gestao-botao" data-upload-cancelar hidden>Cancelar envio</button>
                <noscript>Ative o JavaScript para enviar arquivos.</noscript>
            </div>
        <?php elseif ($tipo === 'textarea'): ?><textarea id="<?= site_escape($id) ?>" name="<?= site_escape($prefixo . '[' . $nome . ']') ?>" maxlength="<?= $max ?>" rows="3"><?= site_escape((string)$valor) ?></textarea>
        <?php else: ?><input id="<?= site_escape($id) ?>" name="<?= site_escape($prefixo . '[' . $nome . ']') ?>" type="<?= $tipo === 'number' ? 'number' : 'text' ?>" value="<?= site_escape((string)$valor) ?>" <?= $tipo === 'number' ? 'min="1" max="' . $max . '"' : 'maxlength="' . $max . '"' ?>><?php endif; ?>
    <?php endif;
}
function editor_itens(string $tipo, string $prefixo, array $itens, array $esquema): void
{
    ?>
    <div class="editor-colecao" data-collection="<?= $tipo ?>" data-name="<?= site_escape($prefixo) ?>">
        <div data-items>
            <?php foreach ($itens ?: [[]] as $i => $item) if (is_array($item)) editor_item($tipo, $prefixo . '[' . $i . ']', $item, $esquema); ?>
        </div><button class="gestao-botao" type="button" data-add="<?= $tipo ?>">Adicionar mais: <?= site_escape($esquema[$tipo][3]) ?></button>
    </div>
<?php
}
function editor_item(string $tipo, string $prefixo, array $item, array $esquema): void
{
    [$t, $pk, $fk, $rotulo, $campos] = $esquema[$tipo];
?>
    <fieldset class="editor-item">
        <legend><?= site_escape($rotulo) ?></legend>
        <input type="hidden" name="<?= site_escape($prefixo . '[' . $pk . ']') ?>" value="<?= (int)($item[$pk] ?? 0) ?>">
        <?php foreach ($campos as $nome => $spec) editor_campo($prefixo, $nome, $spec, $item[$nome] ?? ($spec[1] === 'number' ? 1 : '')); ?>
        <?php if ($tipo === 'questoes'): ?>
            <h3>Alternativas</h3><?php editor_itens('alternativas', $prefixo . '[alternativas]', is_array($item['alternativas'] ?? null) ? $item['alternativas'] : [], $esquema); ?>
            <h3>Imagens da questão</h3><?php editor_itens('imagens', $prefixo . '[imagens]', is_array($item['imagens'] ?? null) ? $item['imagens'] : [], $esquema); ?>
        <?php endif; ?>
        <label class="gestao-check editor-remover"><input type="checkbox" name="<?= site_escape($prefixo . '[remover]') ?>" value="1" <?= !empty($item['remover']) ? 'checked' : '' ?>>Remover este item ao salvar</label>
    </fieldset>
<?php
}
$erro = '';
$aviso = $_SESSION['conteudos_aviso'] ?? '';
unset($_SESSION['conteudos_aviso']);
$dados = ['titulo' => '', 'texto' => '', 'ordem' => 1, 'nivel_dificuldade' => 'medio', 'resumos' => [], 'videos' => [], 'questoes' => []];
$subdivisoes = [];
$materias = [];
$lista = [];
$pronto = false;
$id = editor_id($_GET['editar'] ?? $_GET['excluir'] ?? null);
$excluir = isset($_GET['excluir']);
$revisao = '';
$pai = editor_id($_GET['id_materia'] ?? null);
if (!$permitido) http_response_code(403);
else try {
    $arquivoSubdivisoes = new SubdivisoesArquivo($pdo);
    $subdivisoes = $arquivoSubdivisoes->lista();
    $materias = $pdo->query('SELECT id_materia,titulo FROM materia ORDER BY titulo,id_materia')->fetchAll();
    if ($id) {
        $dados = editor_ler($pdo, $id, $esquema);
        $pai = (int)$dados['id_materia'];
        $revisao = editor_revisao($dados);
    }
    if (!$pai) $pai = (int)($materias[0]['id_materia'] ?? 0);
    if (!in_array($pai, array_map('intval', array_column($materias, 'id_materia')), true)) throw new DomainException('Cadastre ou selecione uma matéria existente.');
    $dados['id_materia'] = $pai;
    $url = 'gerenciar-conteudos.php?id_materia=' . $pai;
    $pronto = true;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            if (!auth_csrf_valido()) {
                http_response_code(403);
                throw new DomainException('O formulário expirou. Atualize a página.');
            }
            if (editor_texto($_POST['completo'] ?? '') !== 'sim') throw new DomainException('O formulário chegou incompleto. Nenhuma alteração foi salva. Reduza a quantidade de itens por envio ou ajuste o limite de formulários do PHP.');
            $acao = editor_texto($_POST['acao'] ?? '');
            if ($automatico && $acao !== 'salvar') throw new DomainException('A exclusão do conteúdo precisa de confirmação.');
            if (!in_array($acao, ['salvar', 'excluir'], true) || ($acao === 'excluir' && (!$excluir || !$id || editor_texto($_POST['confirmar'] ?? '') !== 'sim')) || ($acao === 'salvar' && $excluir)) throw new DomainException('Ação inválida ou exclusão não confirmada.');
            $enviado = is_array($_POST['conteudo'] ?? null) ? $_POST['conteudo'] : [];
            $revisao = editor_texto($_POST['revisao'] ?? '');
            $anterior = $dados;
            if ($acao === 'salvar') $dados = array_merge($dados, $enviado);
            $pdo->beginTransaction();
            $q = $pdo->prepare('SELECT tipo_usuario,ativo FROM usuario WHERE id_usuario=? FOR UPDATE');
            $q->execute([(int)$contaAtual['id_usuario']]);
            $autor = $q->fetch();
            if (!$autor || !(int)$autor['ativo'] || !in_array($autor['tipo_usuario'], ['professor', 'administrador'], true)) throw new DomainException('Sua conta não pode alterar conteúdos.');
            $destino = $acao === 'salvar' ? editor_id($enviado['id_materia'] ?? null) : $pai;
            $q = $pdo->prepare('SELECT id_materia FROM materia WHERE id_materia IN (?,?) ORDER BY id_materia FOR UPDATE');
            $q->execute([$pai, $destino]);
            $pais = array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN));
            if (!in_array($pai, $pais, true) || !in_array($destino, $pais, true)) throw new DomainException('Selecione uma matéria válida.');
            if ($id) {
                $q = $pdo->prepare('SELECT id_conteudo FROM conteudo WHERE id_conteudo=? FOR UPDATE');
                $q->execute([$id]);
                if (!$q->fetchColumn()) throw new DomainException('O conteúdo foi removido.');
                $anterior = editor_ler($pdo, $id, $esquema);
                if (!hash_equals(editor_revisao($anterior), editor_texto($_POST['revisao'] ?? ''))) throw new DomainException('Este conteúdo foi alterado em outra tela. Recarregue a página para ver a versão atual antes de salvar.');
            }
            if ($acao === 'excluir') {
                $q = $pdo->prepare('DELETE FROM conteudo WHERE id_conteudo=?');
                $q->execute([$id]);
                unset($arquivoSubdivisoes->dados['conteudos'][$id]);
            } else {
                $titulo = editor_texto($enviado['titulo'] ?? '');
                $texto = editor_texto($enviado['texto'] ?? '');
                $ordem = editor_id($enviado['ordem'] ?? null);
                $nivel = editor_texto($enviado['nivel_dificuldade'] ?? '');
                if ($titulo === '' || mb_strlen($titulo) > 150 || $texto === '' || mb_strlen($texto) > 100000) throw new DomainException('Preencha o título (até 150 caracteres) e a explicação (até 100.000 caracteres).');
                if (!$ordem || $ordem > 100000 || !in_array($nivel, ['facil', 'medio', 'avancado'], true)) throw new DomainException('Confira a ordem e a dificuldade.');
                $subdivisao = editor_id($enviado['id_subdivisao'] ?? null);
                if ($subdivisao && (int)($arquivoSubdivisoes->dados['grupos'][$subdivisao]['id_materia'] ?? 0) !== $destino) throw new DomainException('Escolha uma subdivisão pertencente à matéria selecionada.');
                $colecoes = [];
                foreach (['resumos', 'videos', 'questoes'] as $tipo) $colecoes[$tipo] = editor_lista($enviado[$tipo] ?? null, $tipo, $id ? ($anterior[$tipo] ?? []) : [], $esquema);
                if ($id) {
                    $q = $pdo->prepare('UPDATE conteudo SET titulo=?,texto=?,id_materia=?,ordem=?,nivel_dificuldade=? WHERE id_conteudo=?');
                    $q->execute([$titulo, $texto, $destino, $ordem, $nivel, $id]);
                } else {
                    $q = $pdo->prepare('INSERT INTO conteudo (titulo,texto,id_materia,ordem,nivel_dificuldade) VALUES (?,?,?,?,?)');
                    $q->execute([$titulo, $texto, $destino, $ordem, $nivel]);
                }
                $idSalvo = $id ?: (int)$pdo->lastInsertId();
                $arquivoSubdivisoes->vincular($idSalvo, $destino, $subdivisao);
                $idsSalvos = [];
                $itensRemovidos = [];
                foreach ($colecoes as $tipo => $itens) editor_gravar($pdo, $idSalvo, $tipo, $itens, $esquema, 'conteudo[' . $tipo . ']', $idsSalvos, $itensRemovidos);
                $q = $pdo->prepare('SELECT COUNT(*) FROM exercicio WHERE id_conteudo=?');
                $q->execute([$idSalvo]);
                if ((int)$q->fetchColumn() < 5) throw new DomainException('Cada conteúdo deve conter pelo menos 5 questões preenchidas. Complete as questões antes de salvar.');
                $novaRevisao = editor_revisao(editor_ler($pdo, $idSalvo, $esquema));
                if ($automatico) {
                    $q = $pdo->prepare('SELECT id_conteudo,titulo FROM conteudo WHERE id_materia=? ORDER BY ordem,id_conteudo');
                    $q->execute([$destino]);
                    $listaAtualizada = $q->fetchAll();
                }
            }
            $arquivoSubdivisoes->confirmar($pdo);
            if ($automatico) {
                $resultado = ['ok' => true, 'id' => $idSalvo, 'materia' => $destino, 'revisao' => $novaRevisao, 'ids' => $idsSalvos, 'removidos' => $itensRemovidos, 'titulo' => $titulo, 'lista' => $listaAtualizada, 'mensagem' => $id ? 'editado com sucesso' : 'criado com sucesso'];
                $_SESSION['editor_pedidos'][$pedido] = ['hash' => hash('sha256', serialize($_POST)), 'resultado' => $resultado];
                while (count($_SESSION['editor_pedidos']) > 20) array_shift($_SESSION['editor_pedidos']);
                editor_json($resultado);
            }
            $_SESSION['conteudos_aviso'] = $acao === 'excluir' ? 'excluído com sucesso' : ($id ? 'editado com sucesso' : 'criado com sucesso');
            header('Location: gerenciar-conteudos.php?id_materia=' . $destino . ($acao === 'salvar' ? '&editar=' . $idSalvo : ''), true, 303);
            exit;
        } catch (DomainException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $erro = $e->getMessage();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('[EnsinoTec] Editor: ' . $e->getCode());
            http_response_code(503);
            $erro = 'Não foi possível salvar. Nenhuma alteração foi aplicada. Tente novamente.';
        }
    }
    $q = $pdo->prepare('SELECT id_conteudo,titulo FROM conteudo WHERE id_materia=? ORDER BY ordem,id_conteudo');
    $q->execute([$pai]);
    $lista = $q->fetchAll();
} catch (DomainException $e) {
    http_response_code(404);
    $erro = $e->getMessage();
} catch (PDOException $e) {
    http_response_code(503);
    error_log('[EnsinoTec] Editor: ' . $e->getCode());
    $erro = 'Não foi possível carregar os conteúdos.';
}
if ($automatico) editor_json(['ok' => false, 'erro' => $erro ?: 'Não foi possível salvar.'], http_response_code() >= 400 ? http_response_code() : 422);
require __DIR__ . '/includes/site/header.php';
?>
<main class="gestao editor-pagina" id="conteudo-principal" tabindex="-1">
    <?php if (!$permitido): ?><h1>Acesso restrito</h1>
        <p>Somente professores e administradores podem gerenciar conteúdos.</p>
    <?php else: ?>
        <header class="gestao-intro">
            <p class="editor-etiqueta">EnsinoTec · Área de ensino</p>
            <h1><?= $id ? 'Editar conteúdo' : 'Adicionar conteúdo' ?></h1>
            <p>Organize sua aula: explicação, resumos, imagens, vídeos e questões.</p>
            <div class="gestao-acoes"><a class="gestao-botao" href="gerenciar-materias.php?id_materia=<?= $pai ?>">← Voltar às matérias</a><a class="gestao-botao" id="editor-ver-pagina" <?= !$id ? 'hidden' : '' ?> href="conteudo.php?id_conteudo=<?= $id ?>">Ver página ↗</a></div>
        </header>
        <?php if ($aviso): ?><p class="gestao-aviso" role="status" data-popup-aviso><?= site_escape($aviso) ?></p><?php endif; ?>
        <?php if ($erro): ?><p class="gestao-aviso" role="alert"><?= site_escape($erro) ?></p><?php endif; ?>
        <?php if ($pronto): ?>
            <div class="editor-layout">
                <section class="gestao-card">
                    <?php if ($excluir): ?>
                        <h2>Excluir conteúdo</h2>
                        <p>Excluir <strong><?= site_escape($dados['titulo']) ?></strong> e todos os resumos, vídeos, imagens e questões vinculados? Esta ação não pode ser desfeita. Os arquivos no servidor serão mantidos.</p>
                        <form method="post" action="<?= site_escape($url . '&excluir=' . $id) ?>"><input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>"><input type="hidden" name="revisao" value="<?= site_escape($revisao) ?>"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="completo" value="sim">
                            <div class="gestao-acoes"><button class="gestao-botao principal" name="confirmar" value="sim">Sim, excluir</button><a class="gestao-botao" href="<?= site_escape($url . '&editar=' . $id) ?>">Cancelar</a></div>
                        </form>
                    <?php else: ?>
                        <form method="post" id="editor-form" action="<?= site_escape($url . ($id ? '&editar=' . $id : '')) ?>">
                            <input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>"><input type="hidden" name="revisao" value="<?= site_escape($revisao) ?>"><input type="hidden" name="acao" value="salvar">
                            <div class="editor-salvamento">
                                <p id="editor-status" role="status" aria-live="polite">Use “Salvar conteúdo” para gravar suas alterações.</p><button class="gestao-botao principal" type="submit">Salvar conteúdo</button>
                            </div>
                            <div class="editor-dados">
                                <h2>Informações do conteúdo</h2>
                                <div class="editor-campo editor-campo-titulo"><?php editor_campo('conteudo', 'titulo', ['Título', 'text', 150], editor_texto($dados['titulo'] ?? '')); ?></div>
                                <div class="editor-campo">
                                    <label for="materia-editor">Matéria</label><select id="materia-editor" name="conteudo[id_materia]" required><?php foreach ($materias as $m): ?><option value="<?= (int)$m['id_materia'] ?>" <?= (int)$m['id_materia'] === editor_id($dados['id_materia'] ?? null) ? 'selected' : '' ?>><?= site_escape($m['titulo']) ?></option><?php endforeach; ?></select>
                                </div>
                                <div class="editor-campo"><label for="subdivisao-editor">Subdivisão</label><select id="subdivisao-editor" name="conteudo[id_subdivisao]">
                                        <option value="">Sem subdivisão</option><?php foreach ($subdivisoes as $sub): ?><option data-materia="<?= (int)$sub['id_materia'] ?>" value="<?= (int)$sub['id_subdivisao'] ?>" <?= editor_id($dados['id_subdivisao'] ?? null) === (int)$sub['id_subdivisao'] ? 'selected' : '' ?>><?= site_escape($sub['titulo']) ?></option><?php endforeach; ?>
                                    </select>
                                    <a id="gerenciar-subdivisoes" href="gerenciar-subdivisoes.php?id_materia=<?= $pai ?>" target="_blank" rel="noopener">Criar, editar ou excluir subdivisões ↗</a><small>Depois de criar uma subdivisão, salve o conteúdo e atualize esta página.</small>

                                </div>
                                <div class="editor-campo"><?php editor_campo('conteudo', 'ordem', ['Ordem', 'number', 100000], $dados['ordem'] ?? 1); ?></div>
                                <div class="editor-campo">
                                    <label for="nivel-editor">Dificuldade</label><select id="nivel-editor" name="conteudo[nivel_dificuldade]"><?php foreach (['facil' => 'Fácil', 'medio' => 'Médio', 'avancado' => 'Avançado'] as $k => $v): ?><option value="<?= $k ?>" <?= ($dados['nivel_dificuldade'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select>
                                </div>
                            </div>
                            <section class="editor-secao">
                                <h2>Resumos e imagens</h2>
                                <p class="gestao-meta">Cada resumo aceita até 255 caracteres. A imagem é opcional.</p><?php editor_itens('resumos', 'conteudo[resumos]', is_array($dados['resumos'] ?? null) ? $dados['resumos'] : [], $esquema); ?>
                            </section>
                            <section class="editor-secao">
                                <h2>Explicação</h2><?php editor_campo('conteudo', 'texto', ['Texto da explicação', 'textarea', 100000], editor_texto($dados['texto'] ?? '')); ?>
                            </section>
                            <section class="editor-secao">
                                <h2>Vídeos</h2>
                                <p class="gestao-meta">Envie um vídeo do seu dispositivo para usar nesta aula.</p><?php editor_itens('videos', 'conteudo[videos]', is_array($dados['videos'] ?? null) ? $dados['videos'] : [], $esquema); ?>
                            </section>
                            <section class="editor-secao">
                                <h2>Questões</h2>
                                <p class="gestao-meta">Obrigatório: pelo menos 5 questões preenchidas. O salvamento automático começa quando esse mínimo for atendido.</p>
                                <p id="editor-questoes-status" class="gestao-meta" role="status" aria-live="polite"></p><?php $questoesEditor = is_array($dados['questoes'] ?? null) ? $dados['questoes'] : [];
                                                                                                                        editor_itens('questoes', 'conteudo[questoes]', array_pad($questoesEditor, max(5, count($questoesEditor)), []), $esquema); ?>
                            </section>
                            <p class="gestao-meta">Itens novos em branco são ignorados. Ao remover um item, confirme a exclusão.</p>
                            <input type="hidden" name="completo" value="sim">
                            <div class="gestao-acoes"><button class="gestao-botao principal" type="submit">Salvar conteúdo</button><a class="gestao-botao" href="<?= site_escape($url) ?>">Cancelar</a></div>
                        </form>
                        <?php foreach (array_keys($esquema) as $tipo): ?><template id="modelo-<?= $tipo ?>"><?php editor_item($tipo, '__PREFIX__', [], $esquema); ?></template><?php endforeach; ?>
                        <script src="assets/js/editor-conteudo.js?v=subdivisoes-1" defer></script>
                        <script src="assets/js/upload-conteudo.js" defer></script>
                    <?php endif; ?>
                </section>
                <aside class="gestao-card editor-lista">
                    <h2>Conteúdos da matéria</h2><a class="gestao-botao" href="<?= site_escape($url) ?>">Novo conteúdo</a><?php foreach ($lista as $item): ?><article>
                            <h3><?= site_escape($item['titulo']) ?></h3>
                            <div class="gestao-acoes"><a class="gestao-botao" href="<?= site_escape($url . '&editar=' . (int)$item['id_conteudo']) ?>">Editar</a><a class="gestao-botao" href="<?= site_escape($url . '&excluir=' . (int)$item['id_conteudo']) ?>">Excluir</a></div>
                        </article><?php endforeach; ?>
                </aside>
            </div><?php endif; ?>
    <?php endif; ?>
</main>
<?php $popupSucesso = $permitido ? $aviso : '';
require __DIR__ . '/includes/site/popup-sucesso.php'; ?>
<?php require __DIR__ . '/includes/site/footer.php'; ?>