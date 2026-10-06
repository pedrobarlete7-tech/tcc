<?php
// ALTERADO: gerencia subdivisões no arquivo JSON, sem alterar o banco.
require_once __DIR__ . '/includes/site/auth.php';
require_once __DIR__ . '/includes/conteudo/subdivisoes.php';
header('Cache-Control: no-store');
if (!$autenticado) {
    header('Location: login.php', true, 303);
    exit;
}
$permitido = in_array($contaAtual['tipo_usuario'] ?? '', ['administrador', 'professor'], true);
if (!$permitido) http_response_code(403);
$id = max(0, (int)($_GET['id_materia'] ?? 0));
$editar = max(0, (int)($_GET['editar'] ?? 0));
$excluir = max(0, (int)($_GET['excluir'] ?? 0));
$erro = '';
$materia = null;
$lista = [];
$selecionada = null;
$aviso = $_SESSION['subdivisao_aviso'] ?? '';
unset($_SESSION['subdivisao_aviso']);
if ($permitido) {
    try {
        $arquivoSubdivisoes = new SubdivisoesArquivo($pdo);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!auth_csrf_valido()) {
                http_response_code(403);
                throw new DomainException('Formulário expirado. Atualize a página.');
            }
            $acao = is_string($_POST['acao'] ?? null) ? $_POST['acao'] : '';
            $titulo = is_string($_POST['titulo'] ?? null) ? trim($_POST['titulo']) : '';
            $sub = max(0, (int)($_POST['id_subdivisao'] ?? 0));
            if (!in_array($acao, ['criar', 'editar', 'excluir'], true)) throw new DomainException('Ação inválida.');
            if ($acao !== 'excluir' && ($titulo === '' || mb_strlen($titulo) > 100)) throw new DomainException('Informe um título de até 100 caracteres.');
            if ($acao === 'excluir' && ($_POST['confirmar'] ?? '') !== 'sim') throw new DomainException('Confirme a exclusão.');
            $pdo->beginTransaction();
            $q = $pdo->prepare('SELECT tipo_usuario,ativo FROM usuario WHERE id_usuario=? FOR UPDATE');
            $q->execute([(int)$contaAtual['id_usuario']]);
            $ator = $q->fetch();
            if (!$ator || !$ator['ativo'] || !in_array($ator['tipo_usuario'], ['administrador', 'professor'], true)) throw new DomainException('Sua conta não pode gerenciar subdivisões.');
            $q = $pdo->prepare('SELECT id_materia FROM materia WHERE id_materia=? FOR UPDATE');
            $q->execute([$id]);
            if (!$q->fetchColumn()) throw new DomainException('Matéria não encontrada.');
            if ($acao !== 'criar') {
                $grupo = $arquivoSubdivisoes->dados['grupos'][$sub] ?? null;
                if (!$grupo || (int)$grupo['id_materia'] !== $id) throw new DomainException('Subdivisão não encontrada nesta matéria.');
                $anterior = $grupo['titulo'];
                if (!is_string($_POST['anterior'] ?? null) || !hash_equals($anterior, $_POST['anterior'])) throw new DomainException('A subdivisão foi alterada. Atualize a página.');
            }
            foreach ($arquivoSubdivisoes->lista() as $g) if ($acao !== 'excluir' && (int)$g['id_materia'] === $id && (int)$g['id_subdivisao'] !== $sub && mb_strtolower($g['titulo']) === mb_strtolower($titulo)) throw new DomainException('Já existe uma subdivisão com esse nome.');
            if ($acao === 'criar') {
                $sub = $arquivoSubdivisoes->dados['proximo']++;
                $arquivoSubdivisoes->dados['grupos'][$sub] = ['id_subdivisao' => $sub, 'id_materia' => $id, 'titulo' => $titulo];
            } elseif ($acao === 'editar') $arquivoSubdivisoes->dados['grupos'][$sub]['titulo'] = $titulo;
            else {
                unset($arquivoSubdivisoes->dados['grupos'][$sub]);
                foreach ($arquivoSubdivisoes->dados['conteudos'] as $c => $g) if ((int)$g === $sub) unset($arquivoSubdivisoes->dados['conteudos'][$c]);
            }
            $arquivoSubdivisoes->confirmar($pdo);
            $_SESSION['subdivisao_aviso'] = ['criar' => 'criado com sucesso', 'editar' => 'editado com sucesso', 'excluir' => 'excluído com sucesso'][$acao];
            header('Location: gerenciar-subdivisoes.php?id_materia=' . $id, true, 303);
            exit;
        }
    } catch (DomainException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $erro = $e->getMessage();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $erro = $e->getCode() === '23000' ? 'Já existe uma subdivisão com esse nome.' : 'Não foi possível salvar as subdivisões agora.';
    }
    try {
        $q = $pdo->prepare('SELECT titulo FROM materia WHERE id_materia=?');
        $q->execute([$id]);
        $materia = $q->fetchColumn();
        if (!isset($arquivoSubdivisoes)) throw new DomainException('Não foi possível abrir o arquivo de subdivisões.');
        $q = $pdo->prepare('SELECT id_conteudo FROM conteudo WHERE id_materia=?');
        $q->execute([$id]);
        $conteudos = $q->fetchAll(PDO::FETCH_COLUMN);
        $lista = array_values(array_filter($arquivoSubdivisoes->lista(), fn($s) => (int)$s['id_materia'] === $id));
        foreach ($lista as &$g) {
            $g['total'] = 0;
            foreach ($conteudos as $c) if (($arquivoSubdivisoes->grupo((int)$c, $id)['id_subdivisao'] ?? 0) === $g['id_subdivisao']) $g['total']++;
        }
        unset($g);
        foreach ($lista as $sub) if ((int)$sub['id_subdivisao'] === ($editar ?: $excluir)) $selecionada = $sub;
    } catch (PDOException | DomainException $e) {
        $erro = 'Não foi possível carregar as subdivisões. Confira a conexão e a permissão da pasta storage.';
    }
}
$tituloPagina = 'Subdivisões — EnsinoTec';
$estilosPagina = ['assets/css/gerenciar-materias.css'];
require __DIR__ . '/includes/site/header.php';
?>
<main class="gestao site-container" id="conteudo-principal" tabindex="-1">
    <?php if (!$permitido): ?><h1>Acesso restrito</h1>
        <p>Somente professores e administradores podem gerenciar subdivisões.</p>
    <?php else: ?>
        <header class="gestao-intro">
            <h1>Subdivisões</h1>
            <p><?= site_escape($materia ?: 'Selecione uma matéria existente.') ?></p><a class="gestao-botao" href="gerenciar-materias.php?id_materia=<?= $id ?>">← Voltar às matérias</a>
        </header>
        <?php if ($erro): ?><p role="alert"><?= site_escape($erro) ?></p><?php endif; ?>
        <?php if ($materia): ?>
            <div class="gestao-grade">
                <section class="gestao-card">
                    <?php if ($excluir && $selecionada): ?>
                        <h2>Excluir subdivisão?</h2>
                        <p>Ao excluir <strong><?= site_escape($selecionada['titulo']) ?></strong>, seus conteúdos continuarão na matéria, sem subdivisão.</p>
                        <form method="post" class="gestao-form"><input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id_subdivisao" value="<?= $excluir ?>"><input type="hidden" name="anterior" value="<?= site_escape($selecionada['titulo']) ?>"><button class="gestao-botao principal" name="confirmar" value="sim">Sim, excluir</button><a class="gestao-botao" href="gerenciar-subdivisoes.php?id_materia=<?= $id ?>">Cancelar</a></form>
                    <?php else: ?>
                        <h2><?= $selecionada ? 'Editar subdivisão' : 'Nova subdivisão' ?></h2>
                        <form method="post" class="gestao-form"><input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>"><input type="hidden" name="acao" value="<?= $selecionada ? 'editar' : 'criar' ?>"><input type="hidden" name="id_subdivisao" value="<?= (int)($selecionada['id_subdivisao'] ?? 0) ?>"><input type="hidden" name="anterior" value="<?= site_escape($selecionada['titulo'] ?? '') ?>"><label for="titulo-sub">Nome da subdivisão</label><input id="titulo-sub" name="titulo" required maxlength="100" placeholder="Ex.: Geometria" value="<?= site_escape($selecionada['titulo'] ?? '') ?>"><button class="gestao-botao principal" style="margin-top:20px" type="submit"><?= $selecionada ? 'Salvar alterações' : 'Criar subdivisão' ?></button><?php if ($selecionada): ?><a href="gerenciar-subdivisoes.php?id_materia=<?= $id ?>">Cancelar</a><?php endif; ?></form>
                    <?php endif; ?>
                </section>
                <section class="gestao-card">
                    <h2>Subdivisões da matéria</h2>
                    <?php foreach ($lista as $sub): ?><article class="gestao-subsecoes">
                            <h3><?= site_escape($sub['titulo']) ?></h3>
                            <p><?= (int)$sub['total'] ?> conteúdo(s)</p>
                            <div class="gestao-acoes"><a class="gestao-botao" href="?id_materia=<?= $id ?>&amp;editar=<?= (int)$sub['id_subdivisao'] ?>">Editar</a><a class="gestao-botao" href="?id_materia=<?= $id ?>&amp;excluir=<?= (int)$sub['id_subdivisao'] ?>">Excluir</a></div>
                        </article><?php endforeach; ?>
                    <?php if (!$lista): ?><p>Nenhuma subdivisão cadastrada.</p><?php endif; ?>
                </section>
            </div>
    <?php endif;
    endif; ?>
</main>
<?php $popupSucesso = $permitido ? $aviso : '';
require __DIR__ . '/includes/site/popup-sucesso.php';
require __DIR__ . '/includes/site/footer.php'; ?>