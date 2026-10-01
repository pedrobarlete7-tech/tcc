<?php
declare(strict_types=1);
// ALTERADO: reúne a gestão das matérias e o acesso aos seus conteúdos.
require_once __DIR__ . '/includes/site/auth.php';
header('Cache-Control: no-store');
if (!$autenticado) {
    header('Location: login.php', true, 303);
    exit;
}
$permitido = in_array($contaAtual['tipo_usuario'] ?? '', ['professor', 'administrador'], true);
$tituloPagina = 'Matérias e conteúdos — EnsinoTec';
$estilosPagina = ['assets/css/gerenciar-materias.css'];
$erro = '';
$aviso = $_SESSION['materias_aviso'] ?? '';
unset($_SESSION['materias_aviso']);
$materias = [];
$selecionada = null;
$excluir = false;
$titulo = '';
$descricao = '';
$carregado = false;
$textoPost = static fn(string $campo): string => is_string($_POST[$campo] ?? null) ? trim($_POST[$campo]) : '';
$idPositivo = static fn($valor): int => (int) (filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0);
if (!$permitido) {
    http_response_code(403);
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            if (!auth_csrf_valido()) {
                http_response_code(403);
                throw new DomainException('Sua sessão de formulário expirou. Atualize a página e tente novamente.');
            }
            $acao = $textoPost('acao');
            $id = $idPositivo($_POST['id_materia'] ?? null);
            $titulo = $textoPost('titulo');
            $descricao = $textoPost('descricao');
            if (!in_array($acao, ['adicionar', 'editar', 'excluir'], true)) throw new DomainException('Ação inválida.');
            if ($acao !== 'adicionar' && !$id) throw new DomainException('Matéria inválida.');
            if ($acao !== 'excluir' && ($titulo === '' || mb_strlen($titulo) > 100 || mb_strlen($descricao) > 5000)) {
                throw new DomainException('Informe um título de até 100 caracteres e uma descrição de até 5.000 caracteres.');
            }
            $pdo->beginTransaction();
            $q = $pdo->prepare('SELECT tipo_usuario, ativo FROM usuario WHERE id_usuario = ? FOR UPDATE');
            $q->execute([(int) $contaAtual['id_usuario']]);
            $autor = $q->fetch();
            if (!$autor || !(int) $autor['ativo'] || !in_array($autor['tipo_usuario'], ['professor', 'administrador'], true)) {
                throw new DomainException('Sua conta não tem permissão para alterar matérias.');
            }
            if ($acao === 'adicionar') {
                $q = $pdo->prepare('INSERT INTO materia (titulo, descricao) VALUES (?, ?)');
                $q->execute([$titulo, $descricao]);
                $mensagem = 'Matéria adicionada.';
            } else {
                $destino = $idPositivo($_POST['destino'] ?? null);
                $q = $pdo->prepare('SELECT id_materia FROM materia WHERE id_materia IN (?, ?) ORDER BY id_materia FOR UPDATE');
                $q->execute([$id, $acao === 'excluir' ? $destino : $id]);
                $ids = array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN));
                if (!in_array($id, $ids, true)) throw new DomainException('Esta matéria não existe mais.');
                if ($acao === 'editar') {
                    $q = $pdo->prepare('UPDATE materia SET titulo = ?, descricao = ? WHERE id_materia = ?');
                    $q->execute([$titulo, $descricao, $id]);
                    $mensagem = 'Matéria atualizada.';
                } else {
                    if ($textoPost('confirmar') !== 'sim') throw new DomainException('Confirme a exclusão da matéria.');
                    $q = $pdo->prepare('SELECT COUNT(*) FROM conteudo WHERE id_materia = ?');
                    $q->execute([$id]);
                    if ((int) $q->fetchColumn() > 0) {
                        if ($destino === $id || !in_array($destino, $ids, true)) throw new DomainException('Escolha outra matéria para receber os conteúdos antes de excluir.');
                        $q = $pdo->prepare('UPDATE conteudo SET id_materia = ? WHERE id_materia = ?');
                        $q->execute([$destino, $id]);
                    }
                    $q = $pdo->prepare('DELETE FROM materia WHERE id_materia = ?');
                    $q->execute([$id]);
                    $mensagem = 'Matéria excluída. Os conteúdos existentes foram preservados.';
                }
            }
            $pdo->commit();
            $_SESSION['materias_aviso'] = $mensagem;
            header('Location: gerenciar-materias.php', true, 303);
            exit;
        } catch (DomainException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $erro = $e->getMessage();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('[EnsinoTec] Falha na gestão de matérias. Código: ' . $e->getCode());
            http_response_code(503);
            $erro = 'Não foi possível salvar. Nenhuma alteração desta operação foi aplicada. Tente novamente.';
        }
    }
    try {
        $materias = $pdo->query('SELECT m.id_materia, m.titulo, m.descricao, (SELECT COUNT(*) FROM conteudo c WHERE c.id_materia = m.id_materia) AS total FROM materia m ORDER BY m.titulo, m.id_materia')->fetchAll();
        $carregado = true;
        $idTela = $idPositivo($_GET['editar'] ?? $_GET['excluir'] ?? null);
        $excluir = isset($_GET['excluir']);
        foreach ($materias as $materia) if ((int) $materia['id_materia'] === $idTela) $selecionada = $materia;
        if ($idTela && !$selecionada) { http_response_code(404); $erro = 'Matéria não encontrada. Escolha uma matéria da lista.'; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $titulo = (string) ($selecionada['titulo'] ?? '');
            $descricao = (string) ($selecionada['descricao'] ?? '');
        }
    } catch (PDOException $e) {
        http_response_code(503);
        error_log('[EnsinoTec] Falha ao listar matérias. Código: ' . $e->getCode());
        $erro = 'Não foi possível carregar as matérias. Tente novamente em instantes.';
    }
}
require __DIR__ . '/includes/site/header.php';
?>
<main class="gestao" id="conteudo-principal" tabindex="-1">
<?php if (!$permitido): ?>
    <h1>Acesso restrito</h1><p>Somente professores e administradores podem gerenciar matérias.</p><a class="gestao-botao" href="index.php">Voltar ao início</a>
<?php else: ?>
    <header class="gestao-intro"><p>EnsinoTec · Área de ensino</p><h1>Matérias e conteúdos</h1><p>Organize as disciplinas e entre em Conteúdos para editar as aulas, mídias e questões.</p></header>
    <?php if ($aviso): ?><p class="gestao-aviso" role="status"><?= site_escape($aviso) ?></p><?php endif; ?>
    <?php if ($erro): ?><p class="gestao-aviso" role="alert"><?= site_escape($erro) ?></p><?php endif; ?>
    <?php if ($carregado): ?>
    <div class="gestao-grade">
    <section class="gestao-card" aria-labelledby="form-titulo">
    <?php if ($excluir && $selecionada): ?>
        <h2 id="form-titulo">Excluir matéria</h2>
        <p>Deseja excluir <strong><?= site_escape($selecionada['titulo']) ?></strong>?</p>
        <form method="post" action="gerenciar-materias.php?excluir=<?= (int) $selecionada['id_materia'] ?>">
            <input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>">
            <input type="hidden" name="acao" value="excluir">
            <input type="hidden" name="id_materia" value="<?= (int) $selecionada['id_materia'] ?>">
            <?php if ((int) $selecionada['total'] > 0): ?>
            <p>Esta matéria possui <?= (int) $selecionada['total'] ?> conteúdo(s). Escolha uma matéria para recebê-los. As aulas e questões serão mantidas.</p>
            <label for="destino">Transferir conteúdos para</label>
            <select name="destino" id="destino" required><option value="">Selecione outra matéria</option>
            <?php foreach ($materias as $opcao): if ($opcao['id_materia'] === $selecionada['id_materia']) continue; ?>
                <option value="<?= (int) $opcao['id_materia'] ?>"><?= site_escape($opcao['titulo']) ?></option>
            <?php endforeach; ?></select>
            <?php if (count($materias) < 2): ?><p>Adicione outra matéria antes de realizar a transferência.</p><?php endif; ?>
            <?php endif; ?>
            <div class="gestao-acoes"><button class="gestao-botao principal" name="confirmar" value="sim" type="submit">Sim, excluir matéria</button><a class="gestao-botao" href="gerenciar-materias.php">Cancelar</a></div>
        </form>
    <?php else: ?>
        <h2 id="form-titulo"><?= $selecionada ? 'Editar matéria' : 'Adicionar matéria' ?></h2>
        <form method="post" action="gerenciar-materias.php<?= $selecionada ? '?editar=' . (int) $selecionada['id_materia'] : '' ?>">
            <input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>">
            <input type="hidden" name="acao" value="<?= $selecionada ? 'editar' : 'adicionar' ?>">
            <input type="hidden" name="id_materia" value="<?= (int) ($selecionada['id_materia'] ?? 0) ?>">
            <label for="titulo">Nome da matéria</label><input id="titulo" name="titulo" maxlength="100" required value="<?= site_escape($titulo) ?>" placeholder="Ex.: Português">
            <label for="descricao">Descrição <span>(opcional)</span></label><textarea id="descricao" name="descricao" rows="5" maxlength="5000"><?= site_escape($descricao) ?></textarea>
            <div class="gestao-acoes"><button class="gestao-botao principal" type="submit"><?= $selecionada ? 'Salvar alterações' : 'Adicionar matéria' ?></button><?php if ($selecionada): ?><a class="gestao-botao" href="gerenciar-materias.php">Cancelar</a><?php endif; ?></div>
        </form>
    <?php endif; ?>
    </section>
    <section class="gestao-lista" aria-labelledby="lista-titulo"><h2 id="lista-titulo">Matérias cadastradas <span>(<?= count($materias) ?>)</span></h2>
        <?php if (!$materias): ?><p>Nenhuma matéria cadastrada. Adicione a primeira pelo formulário.</p><?php endif; ?>
        <?php foreach ($materias as $materia): ?>
        <article class="gestao-card"><div class="gestao-meta">ID <?= (int) $materia['id_materia'] ?> · <?= (int) $materia['total'] ?> conteúdo(s)</div><h3><?= site_escape($materia['titulo']) ?></h3>
            <?php if ($materia['descricao']): ?><p class="gestao-descricao"><?= site_escape($materia['descricao']) ?></p><?php endif; ?>
            <div class="gestao-acoes"><a class="gestao-botao principal" href="gerenciar-conteudos.php?tipo=conteudo&amp;id_materia=<?= (int) $materia['id_materia'] ?>">Conteúdos</a><a class="gestao-botao" href="gerenciar-materias.php?editar=<?= (int) $materia['id_materia'] ?>">Editar<span class="gestao-sr"> <?= site_escape($materia['titulo']) ?></span></a><a class="gestao-botao" href="gerenciar-materias.php?excluir=<?= (int) $materia['id_materia'] ?>">Excluir<span class="gestao-sr"> <?= site_escape($materia['titulo']) ?></span></a></div>
        </article>
        <?php endforeach; ?>
    </section></div>
    <?php endif; ?>
<?php endif; ?>
</main>
<?php require __DIR__ . '/includes/site/footer.php'; ?>

