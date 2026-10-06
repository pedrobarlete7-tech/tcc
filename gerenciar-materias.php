<?php

declare(strict_types=1);
// ALTERADO: mantém a gestão de subdivisões sem depender de novas tabelas.
require_once __DIR__ . '/includes/site/auth.php';
require_once __DIR__ . '/includes/conteudo/subdivisoes.php';
header('Cache-Control: no-store');
if (!$autenticado) {
    header('Location: login.php', true, 303);
    exit;
}
$permitido = in_array($contaAtual['tipo_usuario'] ?? '', ['professor', 'administrador'], true);
$tituloPagina = 'Matérias e conteúdos — EnsinoTec';
$estilosPagina = ['assets/css/gerenciar-materias.css', 'assets/css/painel-materias.css'];
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
            $arquivoSubdivisoes = new SubdivisoesArquivo($pdo);
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
                $id = (int)$pdo->lastInsertId();
            } else {
                $destino = $idPositivo($_POST['destino'] ?? null);
                $q = $pdo->prepare('SELECT id_materia FROM materia WHERE id_materia IN (?, ?) ORDER BY id_materia FOR UPDATE');
                $q->execute([$id, $acao === 'excluir' ? $destino : $id]);
                $ids = array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN));
                if (!in_array($id, $ids, true)) throw new DomainException('Esta matéria não existe mais.');
                if ($acao === 'editar') {
                    $q = $pdo->prepare('UPDATE materia SET titulo = ?, descricao = ? WHERE id_materia = ?');
                    $q->execute([$titulo, $descricao, $id]);
                } else {
                    if ($textoPost('confirmar') !== 'sim') throw new DomainException('Confirme a exclusão da matéria.');
                    $q = $pdo->prepare('SELECT COUNT(*) FROM conteudo WHERE id_materia = ?');
                    $q->execute([$id]);
                    if ((int) $q->fetchColumn() > 0) {
                        if ($destino === $id || !in_array($destino, $ids, true)) throw new DomainException('Escolha outra matéria para receber os conteúdos antes de excluir.');
                        $q = $pdo->prepare('UPDATE conteudo SET id_materia = ? WHERE id_materia = ?');
                        $q->execute([$destino, $id]);
                    }
                    foreach ($arquivoSubdivisoes->dados['grupos'] as $sub => $g) if ((int)$g['id_materia'] === $id) {
                        unset($arquivoSubdivisoes->dados['grupos'][$sub]);
                        foreach ($arquivoSubdivisoes->dados['conteudos'] as $c => $v) if ((int)$v === (int)$sub) unset($arquivoSubdivisoes->dados['conteudos'][$c]);
                    }
                    $q = $pdo->prepare('DELETE FROM materia WHERE id_materia = ?');
                    $q->execute([$id]);
                }
            }
            $arquivoSubdivisoes->confirmar($pdo);
            $_SESSION['materias_aviso'] = ['adicionar' => 'criado com sucesso', 'editar' => 'editado com sucesso', 'excluir' => 'excluído com sucesso'][$acao];
            header('Location: gerenciar-materias.php' . ($acao !== 'excluir' ? '?id_materia=' . $id : ''), true, 303);
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
        if ($idTela && !$selecionada) {
            http_response_code(404);
            $erro = 'Matéria não encontrada. Escolha uma matéria da lista.';
        }
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
$materiaAtiva = null;
$conteudosPainel = [];
$mostrarForm = isset($_GET['nova']) || $selecionada || ($_SERVER['REQUEST_METHOD'] === 'POST' && $erro !== '');
if ($permitido && $carregado) {
    $idAtivo = (int)($selecionada['id_materia'] ?? ($idPositivo($_GET['id_materia'] ?? null) ?: ($materias[0]['id_materia'] ?? 0)));
    foreach ($materias as $m) if ((int)$m['id_materia'] === $idAtivo) $materiaAtiva = $m;
    if ($materiaAtiva) {
        try {
            $q = $pdo->prepare('SELECT c.id_conteudo,c.titulo,c.ordem,c.nivel_dificuldade,LEFT(c.texto,160) AS previa FROM conteudo c WHERE c.id_materia=? ORDER BY c.ordem,c.id_conteudo');
            $q->execute([$idAtivo]);
            $conteudosPainel = $q->fetchAll();
        } catch (PDOException $e) {
            http_response_code(503);
            $erro = 'Não foi possível carregar os conteúdos desta matéria.';
        }
    } elseif ($idAtivo) {
        http_response_code(404);
        $erro = 'Matéria não encontrada. Selecione outra disciplina.';
    }
}
$totalConteudos = array_sum(array_column($materias, 'total'));
$semConteudos = count(array_filter($materias, static fn($m) => (int)$m['total'] === 0));
$busca = is_string($_GET['busca'] ?? null) ? mb_substr(trim($_GET['busca']), 0, 150) : '';
$resultadosMaterias = [];
$resultadosConteudos = [];
$buscaCarregada = false;
if ($permitido && $carregado && $busca !== '') {
    try {
        $termo = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $busca) . '%';
        $q = $pdo->prepare("SELECT id_materia, titulo FROM materia WHERE titulo LIKE ? ESCAPE '!' ORDER BY titulo, id_materia");
        $q->execute([$termo]);
        $resultadosMaterias = $q->fetchAll();
        $q = $pdo->prepare("SELECT c.id_conteudo, c.id_materia, c.titulo, m.titulo AS materia FROM conteudo c JOIN materia m ON m.id_materia = c.id_materia WHERE c.titulo LIKE ? ESCAPE '!' ORDER BY m.titulo, c.ordem, c.id_conteudo");
        $q->execute([$termo]);
        $resultadosConteudos = $q->fetchAll();
        $buscaCarregada = true;
    } catch (PDOException $e) {
        http_response_code(503);
        $erro = 'Não foi possível realizar a busca. Tente novamente.';
        error_log('[EnsinoTec] Falha na busca de matérias. Código: ' . $e->getCode());
    }
}
require __DIR__ . '/includes/site/header.php';
?>
<main class="gestao painel-materias" id="conteudo-principal" tabindex="-1">
    <?php if (!$permitido): ?>
        <h1>Acesso restrito</h1>
        <p>Somente professores e administradores podem gerenciar matérias e conteúdos.</p><a class="gestao-botao" href="index.php">Voltar ao início</a>
    <?php else: ?>
        <header class="painel-cabecalho">
            <div>
                <p class="painel-etiqueta"><?= ($contaAtual['tipo_usuario'] ?? '') === 'administrador' ? 'Administração' : 'Área de ensino' ?></p>
                <h1>Matérias e conteúdos</h1>
                <p class="painel-descricao">Organize as matérias da plataforma e gerencie os conteúdos disponíveis para os alunos.</p>
            </div><a class="gestao-botao principal" href="gerenciar-materias.php?nova=1#form-titulo">+ Nova matéria</a>
        </header>
        <?php if ($aviso): ?><p class="gestao-aviso" role="status" data-popup-aviso><?= site_escape($aviso) ?></p><?php endif; ?>
        <?php if ($erro): ?><p class="gestao-aviso" role="alert"><?= site_escape($erro) ?></p><?php endif; ?>
        <?php if ($carregado): ?>
            <form class="painel-busca-geral" method="get" action="gerenciar-materias.php" role="search" aria-label="Buscar matérias e conteúdos">
                <label for="busca-geral">Buscar matérias e conteúdos</label>
                <div><input id="busca-geral" type="search" name="busca" value="<?= site_escape($busca) ?>" maxlength="150" placeholder="Digite o nome da matéria ou do conteúdo…" required><button type="submit" class="gestao-botao principal">Buscar</button><?php if ($busca !== ''): ?><a class="gestao-botao" href="gerenciar-materias.php">Limpar busca</a><?php endif; ?></div>
            </form>
            <?php if ($buscaCarregada): ?>
                <section class="painel-resultados" aria-labelledby="resultados-titulo">
                    <h2 id="resultados-titulo">Resultados para “<?= site_escape($busca) ?>”</h2>
                    <p><?= count($resultadosMaterias) ?> matéria(s) e <?= count($resultadosConteudos) ?> conteúdo(s) encontrado(s).</p>
                    <?php if (!$resultadosMaterias && !$resultadosConteudos): ?><p>Nenhum resultado encontrado. Tente outro nome.</p><?php endif; ?>
                    <?php if ($resultadosMaterias): ?><h3>Matérias</h3>
                        <ul><?php foreach ($resultadosMaterias as $resultado): ?><li><a href="gerenciar-materias.php?id_materia=<?= (int)$resultado['id_materia'] ?>"><?= site_escape($resultado['titulo']) ?><span>Ver conteúdos →</span></a></li><?php endforeach; ?></ul><?php endif; ?>
                    <?php if ($resultadosConteudos): ?><h3>Conteúdos</h3>
                        <ul><?php foreach ($resultadosConteudos as $resultado): ?><li><a href="gerenciar-conteudos.php?id_materia=<?= (int)$resultado['id_materia'] ?>&amp;editar=<?= (int)$resultado['id_conteudo'] ?>"><strong><?= site_escape($resultado['titulo']) ?></strong><span><?= site_escape($resultado['materia']) ?> · Editar conteúdo →</span></a></li><?php endforeach; ?></ul><?php endif; ?>
                </section>
            <?php endif; ?>
            <section class="painel-totais" aria-label="Resumo do portal">
                <article><span>Matérias</span><strong><?= count($materias) ?></strong><small>cadastradas</small></article>
                <article><span>Conteúdos</span><strong><?= (int)$totalConteudos ?></strong><small>cadastrados</small></article>
                <article><span>Matérias sem conteúdo</span><strong><?= $semConteudos ?></strong><small>aguardando conteúdo</small></article>
            </section>
            <div class="painel-layout">
                <aside class="painel-disciplinas">
                    <header>
                        <div>
                            <p class="painel-etiqueta">Matérias</p>
                            <h2>Disciplinas</h2>
                        </div><span class="painel-contador"><?= count($materias) ?></span>
                    </header>
                    <div class="painel-busca" hidden><label class="gestao-sr" for="buscar-materia">Buscar matéria</label><input type="search" id="buscar-materia" placeholder="Buscar matéria…" autocomplete="off"></div>
                    <nav aria-label="Disciplinas cadastradas">
                        <ul id="lista-disciplinas">
                            <?php foreach ($materias as $m): ?><li data-materia="<?= site_escape($m['titulo']) ?>"><a href="gerenciar-materias.php?id_materia=<?= (int)$m['id_materia'] ?>" <?= (int)$m['id_materia'] === (int)($materiaAtiva['id_materia'] ?? 0) ? 'aria-current="page"' : '' ?>><span class="disciplina-inicial" aria-hidden="true"><?= site_escape(mb_strtoupper(mb_substr($m['titulo'], 0, 1))) ?></span><span class="disciplina-nome"><strong><?= site_escape($m['titulo']) ?></strong><small><?= (int)$m['total'] ?> conteúdo(s)</small></span><span class="disciplina-seta" aria-hidden="true">›</span></a></li><?php endforeach; ?>
                        </ul>
                    </nav>
                    <p class="painel-vazio" id="busca-vazia" role="status" hidden>Nenhuma matéria encontrada.</p>
                    <?php if (!$materias): ?><p class="painel-vazio">Cadastre a primeira matéria para começar.</p><?php endif; ?>
                </aside>
                <section class="painel-conteudos" aria-label="Gestão da matéria selecionada">
                    <?php if ($mostrarForm): ?>
                        <section class="painel-form" aria-labelledby="form-titulo">
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
                                        <select name="destino" id="destino" required>
                                            <option value="">Selecione outra matéria</option>
                                            <?php foreach ($materias as $opcao): if ($opcao['id_materia'] === $selecionada['id_materia']) continue; ?>
                                                <option value="<?= (int) $opcao['id_materia'] ?>"><?= site_escape($opcao['titulo']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (count($materias) < 2): ?><p>Adicione outra matéria antes de realizar a transferência.</p><?php endif; ?>
                                    <?php endif; ?>
                                    <div class="gestao-acoes"><button class="gestao-botao principal" name="confirmar" value="sim" type="submit">Sim, excluir matéria</button><a class="gestao-botao" href="gerenciar-materias.php?id_materia=<?= (int)($selecionada['id_materia'] ?? 0) ?>">Cancelar</a></div>
                                </form>
                            <?php else: ?>
                                <h2 id="form-titulo"><?= $selecionada ? 'Editar matéria' : 'Adicionar matéria' ?></h2>
                                <form method="post" action="gerenciar-materias.php<?= $selecionada ? '?editar=' . (int) $selecionada['id_materia'] : '' ?>">
                                    <input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>">
                                    <input type="hidden" name="acao" value="<?= $selecionada ? 'editar' : 'adicionar' ?>">
                                    <input type="hidden" name="id_materia" value="<?= (int) ($selecionada['id_materia'] ?? 0) ?>">
                                    <label for="titulo">Nome da matéria</label><input id="titulo" name="titulo" maxlength="100" required value="<?= site_escape($titulo) ?>" placeholder="Ex.: Português">
                                    <label for="descricao">Descrição <span>(opcional)</span></label><textarea id="descricao" name="descricao" rows="5" maxlength="5000"><?= site_escape($descricao) ?></textarea>
                                    <div class="gestao-acoes"><button class="gestao-botao principal" type="submit"><?= $selecionada ? 'Salvar alterações' : 'Adicionar matéria' ?></button><a class="gestao-botao" href="gerenciar-materias.php<?= $materiaAtiva ? '?id_materia=' . (int)$materiaAtiva['id_materia'] : '' ?>">Cancelar</a></div>
                                </form>
                            <?php endif; ?>
                        </section>
                    <?php elseif ($materiaAtiva): ?>
                        <header class="painel-materia-cabecalho">
                            <div>
                                <p class="painel-etiqueta">Matéria selecionada</p>
                                <h2><?= site_escape($materiaAtiva['titulo']) ?></h2>
                                <p class="painel-descricao"><?= site_escape($materiaAtiva['descricao'] ?: 'Gerencie os conteúdos desta disciplina.') ?></p>
                            </div>
                            <div class="painel-acoes"><a class="gestao-botao" href="gerenciar-materias.php?editar=<?= $idAtivo ?>#form-titulo">Editar matéria</a><a class="gestao-botao painel-excluir" href="gerenciar-materias.php?excluir=<?= $idAtivo ?>#form-titulo">Excluir matéria</a></div>
                        </header>
                        <div class="painel-ferramentas"><a class="gestao-botao principal" href="gerenciar-conteudos.php?id_materia=<?= $idAtivo ?>">+ Adicionar conteúdo</a><a class="gestao-botao" href="gerenciar-subdivisoes.php?id_materia=<?= $idAtivo ?>">Gerenciar subdivisões</a></div>
                        <?php if ($conteudosPainel): ?>
                            <div class="painel-tabela">
                                <table>
                                    <caption class="gestao-sr">Conteúdos de <?= site_escape($materiaAtiva['titulo']) ?></caption>
                                    <thead>
                                        <tr>
                                            <th scope="col">Ordem</th>
                                            <th scope="col">Conteúdo</th>
                                            <th scope="col">Nível</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($conteudosPainel as $c): $nivel = in_array($c['nivel_dificuldade'], ['facil', 'medio', 'avancado'], true) ? $c['nivel_dificuldade'] : 'medio'; ?>
                                            <tr>
                                                <td data-label="Ordem" class="coluna-ordem"><?= str_pad((string)(int)$c['ordem'], 2, '0', STR_PAD_LEFT) ?></td>
                                                <td class="coluna-conteudo"><a href="conteudo.php?id_conteudo=<?= (int)$c['id_conteudo'] ?>"><?= site_escape($c['titulo']) ?></a>
                                                    <p><?= site_escape(mb_strimwidth($c['previa'], 0, 105, '…')) ?></p>
                                                </td>
                                                <td data-label="Nível"><span class="painel-tag nivel-<?= $nivel ?>"><?= ['facil' => 'Fácil', 'medio' => 'Médio', 'avancado' => 'Avançado'][$nivel] ?></span></td>
                                                <td data-label="Status"><span class="painel-tag status-disponivel">Disponível</span></td>
                                                <td class="coluna-acoes"><a href="gerenciar-conteudos.php?id_materia=<?= $idAtivo ?>&amp;editar=<?= (int)$c['id_conteudo'] ?>">Editar<span class="gestao-sr"> <?= site_escape($c['titulo']) ?></span></a><a class="painel-excluir" href="gerenciar-conteudos.php?id_materia=<?= $idAtivo ?>&amp;excluir=<?= (int)$c['id_conteudo'] ?>">Excluir<span class="gestao-sr"> <?= site_escape($c['titulo']) ?></span></a></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?><div class="painel-vazio">
                                <h3>Nenhum conteúdo nesta matéria</h3>
                                <p>Use “Adicionar conteúdo” para criar a primeira aula.</p>
                            </div><?php endif; ?>
                    <?php else: ?><div class="painel-vazio">
                            <h2>Selecione uma matéria</h2>
                            <p>Escolha uma disciplina na lista ou cadastre uma nova.</p>
                        </div><?php endif; ?>
                </section>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>
<script src="assets/js/painel-materias.js" defer></script>
<?php $popupSucesso = $permitido ? $aviso : '';
require __DIR__ . '/includes/site/popup-sucesso.php'; ?>
<?php require __DIR__ . '/includes/site/footer.php'; ?>