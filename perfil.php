<?php
// ALTERADO: permite solicitar acesso de professor e acompanhar a resposta no perfil.
require_once __DIR__ . '/includes/site/auth.php';
header('Cache-Control: no-store');
if (!$autenticado) {
    header('Location: login.php', true, 303);
    exit;
}
$nomePerfil = (string) $contaAtual['nome'];
$emailPerfil = (string) $contaAtual['email'];
$nomeUsuario = $nomePerfil;
require_once __DIR__ . '/includes/site/professores.php';
$avisoPedido = $_SESSION['aviso_professor'] ?? '';
unset($_SESSION['aviso_professor']);
$erroPedido = '';
$pedidoAtual = null;
$pedidosDisponiveis = true;
$mensagemPedido = '';
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!auth_csrf_valido() || ($_POST['acao'] ?? '') !== 'solicitar_professor') {
            http_response_code(403);
            throw new RuntimeException('Solicitação inválida. Atualize a página e tente novamente.');
        }
        $mensagemPedido = is_string($_POST['mensagem'] ?? null) ? trim($_POST['mensagem']) : '';
        professor_solicitar($pdo, (int) $contaAtual['id_usuario'], $mensagemPedido);
        $_SESSION['aviso_professor'] = 'Pedido enviado! Aguarde a análise de um administrador.';
        header('Location: perfil.php', true, 303); exit;
    }
} catch (PDOException $e) {
    error_log('[EnsinoTec] Falha ao enviar pedido de professor. Código: ' . $e->getCode());
    $erroPedido = 'Não foi possível enviar seu pedido agora. Tente novamente mais tarde.';
} catch (RuntimeException $e) { $erroPedido = $e->getMessage(); }
try {
    $consultaPedido = $pdo->prepare("SELECT mensagem, status, data_solicitacao, data_resposta, observacao_admin FROM solicitacao_professor WHERE id_usuario = ? ORDER BY (status = 'pendente') DESC, id_solicitacao DESC LIMIT 1");
    $consultaPedido->execute([(int) $contaAtual['id_usuario']]);
    $pedidoAtual = $consultaPedido->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (PDOException $e) { $pedidosDisponiveis = false; $erroPedido = 'Não foi possível carregar suas solicitações agora.'; }
$tituloPagina = 'Meu perfil — EnsinoTec';
$estilosPagina = ['assets/css/perfil.css', 'assets/css/professores.css'];
require __DIR__ . '/includes/site/header.php';
?>
<main class="pagina-perfil" id="conteudo-principal" tabindex="-1">
    <div class="container">
        <a class="btn-voltar" href="index.php">← VOLTAR</a>
        <div class="perfil">
            <div class="foto">
                <?php if (is_file(__DIR__ . '/assets/img/avatar.png')): ?>
                <img src="assets/img/avatar.png" alt="Avatar padrão" width="130" height="130">
                <?php else: ?>
                <svg class="perfil-avatar" viewBox="0 0 80 80" role="img" aria-label="Avatar padrão"><circle cx="40" cy="28" r="14"/><path d="M13 74v-4c0-17 10-26 27-26s27 9 27 26v4Z"/></svg>
                <?php endif; ?>
            </div>
            <div class="informacoes">
                <h1>Meu perfil</h1>
                <div class="campo">
                    <span class="label">Nome</span>
                    <p><?= site_escape($nomePerfil) ?></p>
                </div>
                <div class="campo">
                    <span class="label">E-mail</span>
                    <p><?= site_escape($emailPerfil) ?></p>
                </div>
                <section class="professor-area" aria-labelledby="professor-titulo">
                    <h2 id="professor-titulo">Acesso de professor</h2>
                    <?php if ($avisoPedido): ?><p class="pedido-aviso" role="status"><?= site_escape($avisoPedido) ?></p><?php endif; ?>
                    <?php if ($erroPedido): ?><p class="pedido-aviso" role="alert"><?= site_escape($erroPedido) ?></p><?php endif; ?>
                    <?php if ($contaAtual['tipo_usuario'] === 'professor'): ?>
                    <p>Sua conta já tem acesso de professor.</p>
                    <?php elseif ($contaAtual['tipo_usuario'] === 'administrador'): ?>
                    <p>Sua conta é administradora. Analise os pedidos no painel administrativo.</p>
                    <a class="pedido-botao" href="solicitacoes-professor.php">Ver solicitações</a>
                    <?php endif; ?>
                    <?php if ($pedidoAtual): ?>
                    <p class="pedido-status">Último pedido: <strong><?= site_escape(['pendente' => 'Aguardando análise', 'aprovada' => 'Aprovado', 'recusada' => 'Recusado'][$pedidoAtual['status']]) ?></strong></p>
                    <?php if ($pedidoAtual['observacao_admin']): ?><p class="pedido-resposta"><strong>Resposta do administrador:</strong><br><?= nl2br(site_escape($pedidoAtual['observacao_admin'])) ?></p><?php endif; ?>
                    <?php endif; ?>
                    <?php if ($pedidosDisponiveis && $contaAtual['tipo_usuario'] === 'aluno' && ($pedidoAtual['status'] ?? '') !== 'pendente'): ?>
                    <p>Envie uma solicitação para que um administrador avalie seu acesso de professor.</p>
                    <form action="perfil.php" method="post" class="pedido-form">
                        <input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>">
                        <input type="hidden" name="acao" value="solicitar_professor">
                        <label for="mensagem-professor">Mensagem para o administrador (opcional)</label>
                        <textarea id="mensagem-professor" name="mensagem" rows="4" maxlength="2000" placeholder="Conte sobre sua atuação ou as matérias que deseja ensinar."><?= site_escape($mensagemPedido) ?></textarea>
                        <button class="pedido-botao" type="submit">Solicitar acesso de professor</button>
                    </form>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>
</main>
<?php require __DIR__ . '/includes/site/footer.php'; ?>
