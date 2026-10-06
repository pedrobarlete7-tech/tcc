<?php
// NOVO: gerencia usuários com busca, filtros, edição e confirmação de acesso.
require_once __DIR__ . '/includes/site/auth.php';
require_once __DIR__ . '/includes/site/usuarios-admin.php';
header('Cache-Control: no-store');
if (!$autenticado) {
    header('Location: login.php', true, 303);
    exit;
}
$permitido = ($contaAtual['tipo_usuario'] ?? '') === 'administrador';
if (!$permitido) http_response_code(403);
$texto = static fn($valor): string => is_string($valor) ? trim($valor) : '';
$busca = mb_substr($texto($_GET['busca'] ?? ''), 0, 100);
$tipos = ['aluno' => 'Aluno', 'professor' => 'Professor', 'administrador' => 'Administrador'];
$tipoFiltro = $texto($_GET['tipo'] ?? '');
if (!isset($tipos[$tipoFiltro])) $tipoFiltro = '';
$situacao = $texto($_GET['situacao'] ?? '');
if (!in_array($situacao, ['0', '1'], true)) $situacao = '';
$pagina = max(1, min(100000, (int)($_GET['pagina'] ?? 1)));
$editar = max(0, (int)($_GET['editar'] ?? 0));
$url = static fn(array $p = []): string => 'gerenciar-usuarios.php?' . http_build_query(array_merge(['busca' => $busca, 'tipo' => $tipoFiltro, 'situacao' => $situacao, 'pagina' => $pagina], $p));
$erro = '';
$usuarios = [];
$contaEditar = null;
$total = 0;
$aviso = $_SESSION['usuarios_aviso'] ?? '';
unset($_SESSION['usuarios_aviso']);
if ($permitido) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            if (!auth_csrf_valido()) {
                http_response_code(403);
                throw new DomainException('Solicitação expirada. Atualize a página.');
            }
            $ativo = $texto($_POST['ativo'] ?? '');
            if (!in_array($ativo, ['0', '1'], true)) throw new DomainException('Situação inválida.');
            usuarios_admin_salvar($pdo, (int)$contaAtual['id_usuario'], (int)($_POST['id'] ?? 0), $texto($_POST['nome'] ?? ''), $texto($_POST['email'] ?? ''), $texto($_POST['tipo_usuario'] ?? ''), (int)$ativo, $texto($_POST['revisao'] ?? ''));
            $_SESSION['usuarios_aviso'] = 'editado com sucesso';
            header('Location: ' . $url(['editar' => null]), true, 303);
            exit;
        } catch (DomainException $e) {
            $erro = $e->getMessage();
        } catch (PDOException $e) {
            $erro = $e->getCode() === '23000' ? 'Este e-mail já está em uso.' : 'Não foi possível salvar. Tente novamente.';
        }
    }
    try {
        $where = ['1=1'];
        $params = [];
        if ($busca !== '') {
            $where[] = "(nome LIKE ? ESCAPE '!' OR email LIKE ? ESCAPE '!')";
            $termo = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $busca) . '%';
            $params[] = $termo;
            $params[] = $termo;
        }
        if ($tipoFiltro !== '') {
            $where[] = 'tipo_usuario=?';
            $params[] = $tipoFiltro;
        }
        if ($situacao !== '') {
            $where[] = 'ativo=?';
            $params[] = $situacao;
        }
        $sql = implode(' AND ', $where);
        $q = $pdo->prepare('SELECT COUNT(*) FROM usuario WHERE ' . $sql);
        $q->execute($params);
        $total = (int)$q->fetchColumn();
        $pagina = min($pagina, max(1, (int)ceil($total / 20)));
        $q = $pdo->prepare('SELECT id_usuario,nome,email,tipo_usuario,ativo FROM usuario WHERE ' . $sql . ' ORDER BY nome,id_usuario LIMIT 20 OFFSET ?');
        foreach ($params as $i => $v) $q->bindValue($i + 1, $v);
        $q->bindValue(count($params) + 1, ($pagina - 1) * 20, PDO::PARAM_INT);
        $q->execute();
        $usuarios = $q->fetchAll(PDO::FETCH_ASSOC);
        if ($editar) {
            $q = $pdo->prepare('SELECT id_usuario,nome,email,tipo_usuario,ativo FROM usuario WHERE id_usuario=?');
            $q->execute([$editar]);
            $contaEditar = $q->fetch(PDO::FETCH_ASSOC);
            if (!$contaEditar) $erro = 'Conta não encontrada.';
        }
    } catch (PDOException $e) {
        $erro = 'Não foi possível carregar as contas agora.';
    }
}
$tituloPagina = 'Gerenciar usuários — EnsinoTec';
$estilosPagina = ['assets/css/administracao.css?v=2', 'assets/css/gerenciar-usuarios.css'];
require __DIR__ . '/includes/site/header.php';
?>
<main class="administracao site-container usuarios" id="conteudo-principal" tabindex="-1">
    <?php if (!$permitido): ?>
        <h1>Acesso restrito</h1>
        <p>Somente administradores podem gerenciar usuários.</p>
    <?php else: ?>
        <a class="admin-link" href="administracao.php">← Voltar à administração</a>
        <h1>Gerenciar usuários</h1>
        <p class="admin-descricao">Consulte as contas e gerencie os dados e o acesso ao EnsinoTec.</p>
        <?php if ($erro): ?><p class="usuarios-alerta" role="alert"><?= site_escape($erro) ?></p><?php endif; ?>
        <?php if ($contaEditar): ?>
            <section class="usuarios-painel" aria-labelledby="editar-titulo">
                <h2 id="editar-titulo">Editar conta</h2>
                <form method="post" action="<?= site_escape($url(['editar' => $editar])) ?>" class="usuarios-editar" id="editar-usuario">
                    <input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>">
                    <input type="hidden" name="id" value="<?= $editar ?>">
                    <input type="hidden" name="revisao" value="<?= site_escape(usuarios_admin_revisao($contaEditar)) ?>">
                    <label>Nome completo<input name="nome" required minlength="2" maxlength="100" value="<?= site_escape($contaEditar['nome']) ?>"></label>
                    <label>E-mail<input type="email" name="email" required maxlength="100" value="<?= site_escape($contaEditar['email']) ?>"></label>
                    <label>Perfil<select name="tipo_usuario" data-original="<?= site_escape($contaEditar['tipo_usuario']) ?>"><?php foreach ($tipos as $v => $r): ?><option value="<?= $v ?>" <?= $contaEditar['tipo_usuario'] === $v ? 'selected' : '' ?>><?= $r ?></option><?php endforeach; ?></select></label>
                    <label>Situação<select name="ativo" data-original="<?= (int)$contaEditar['ativo'] ?>">
                            <option value="1" <?= $contaEditar['ativo'] ? 'selected' : '' ?>>Ativa</option>
                            <option value="0" <?= !$contaEditar['ativo'] ? 'selected' : '' ?>>Desativada</option>
                        </select></label>
                    <p class="usuarios-nota">Uma conta desativada não poderá entrar no site. Alterações de e-mail ou acesso encerram as sessões anteriores.</p>
                    <div class="usuarios-acoes"><button class="usuarios-botao" type="submit">Salvar alterações</button><a class="admin-link" href="<?= site_escape($url()) ?>">Cancelar</a></div>
                </form>
            </section>
        <?php endif; ?>
        <form method="get" class="usuarios-painel usuarios-filtros" role="search" aria-label="Buscar usuários">
            <label>Nome ou e-mail<input type="search" name="busca" maxlength="100" placeholder="Buscar usuário..." value="<?= site_escape($busca) ?>"></label>
            <label>Perfil<select name="tipo">
                    <option value="">Todos os perfis</option><?php foreach ($tipos as $v => $r): ?><option value="<?= $v ?>" <?= $tipoFiltro === $v ? 'selected' : '' ?>><?= $r ?></option><?php endforeach; ?>
                </select></label>
            <label>Situação<select name="situacao">
                    <option value="">Todas</option>
                    <option value="1" <?= $situacao === '1' ? 'selected' : '' ?>>Ativas</option>
                    <option value="0" <?= $situacao === '0' ? 'selected' : '' ?>>Desativadas</option>
                </select></label>
            <button type="submit" class="usuarios-botao">Buscar</button><a class="admin-link" href="gerenciar-usuarios.php">Limpar</a>
        </form>
        <p><?= $total ?> conta(s) encontrada(s) · Página <?= $pagina ?></p>
        <div class="usuarios-lista">
            <?php foreach ($usuarios as $u): ?>
                <article class="usuarios-card">
                    <div class="usuarios-avatar" aria-hidden="true"><?= site_escape(mb_strtoupper(mb_substr($u['nome'], 0, 1))) ?></div>
                    <div class="usuarios-dados">
                        <h2><?= site_escape($u['nome']) ?><?= (int)$u['id_usuario'] === (int)$contaAtual['id_usuario'] ? ' · Você' : '' ?></h2>
                        <p><?= site_escape($u['email']) ?></p><span class="usuarios-tag"><?= site_escape($tipos[$u['tipo_usuario']] ?? $u['tipo_usuario']) ?></span> <span class="usuarios-tag"><?= $u['ativo'] ? 'Ativa' : 'Desativada' ?></span>
                    </div><a class="admin-link" aria-label="Editar <?= site_escape($u['nome']) ?>" href="<?= site_escape($url(['pagina' => $pagina, 'editar' => (int)$u['id_usuario']])) ?>#editar-titulo">Editar</a>
                </article>
            <?php endforeach; ?>
            <?php if (!$usuarios && !$erro): ?><p class="usuarios-painel">Nenhuma conta encontrada com esses filtros.</p><?php endif; ?>
        </div>
        <nav class="usuarios-acoes" aria-label="Páginas de usuários"><?php if ($pagina > 1): ?><a class="admin-link" href="<?= site_escape($url(['pagina' => $pagina - 1])) ?>">Anterior</a><?php endif; ?><?php if ($pagina * 20 < $total): ?><a class="admin-link" href="<?= site_escape($url(['pagina' => $pagina + 1])) ?>">Próxima</a><?php endif; ?></nav>
        <dialog class="usuarios-dialog" id="confirmar-usuario" aria-labelledby="confirmar-titulo">
            <h2 id="confirmar-titulo">Confirmar mudança de acesso?</h2>
            <p id="confirmar-texto"></p>
            <div class="usuarios-acoes"><button type="button" class="usuarios-botao" id="confirmar-salvar">Sim, salvar</button><button type="button" class="admin-link" id="cancelar-salvar">Cancelar</button></div>
        </dialog>
        <script src="assets/js/gerenciar-usuarios.js" defer></script>
    <?php endif; ?>
</main>
<?php $popupSucesso = $permitido ? $aviso : '';
require __DIR__ . '/includes/site/popup-sucesso.php';
require __DIR__ . '/includes/site/footer.php'; ?>