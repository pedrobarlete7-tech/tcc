<?php
// ALTERADO: mostra a foto enviada pelo usuário no cartão do perfil.


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
        header('Location: perfil.php', true, 303);
        exit;
    }
} catch (PDOException $e) {
    error_log('[EnsinoTec] Falha ao enviar pedido de professor. Código: ' . $e->getCode());
    $erroPedido = 'Não foi possível enviar seu pedido agora. Tente novamente mais tarde.';
} catch (RuntimeException $e) {
    $erroPedido = $e->getMessage();
}
try {
    $consultaPedido = $pdo->prepare("SELECT mensagem, status, data_solicitacao, data_resposta, observacao_admin FROM solicitacao_professor WHERE id_usuario = ? ORDER BY (status = 'pendente') DESC, id_solicitacao DESC LIMIT 1");
    $consultaPedido->execute([(int) $contaAtual['id_usuario']]);
    $pedidoAtual = $consultaPedido->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (PDOException $e) {
    $pedidosDisponiveis = false;
    $erroPedido = 'Não foi possível carregar suas solicitações agora.';
}
$tituloPagina = 'Meu perfil — EnsinoTec';
$estilosPagina = ['assets/css/perfil.css?v=foto-1', 'assets/css/professores.css'];
require __DIR__ . '/includes/site/header.php';
?>
<main class="conta-pagina" id="conteudo-principal" tabindex="-1">
    <div class="conta-container">
        <a class="conta-voltar" href="configuracoes.php">← Voltar às configurações</a>
        <header class="conta-cabecalho"><span class="conta-etiqueta">MINHA CONTA</span>
            <h1>Meu perfil</h1>
            <p>Seus dados e seu espaço no EnsinoTec.</p>
        </header>
        <div class="conta-grade">
            <aside class="conta-identidade">
                <div class="conta-capa"></div>
                <div class="conta-avatar" aria-hidden="true"><?php if ($fotoUsuario): ?><img src="<?= site_escape($fotoUsuario) ?>" alt=""><?php else: ?><?= site_escape(mb_strtoupper(mb_substr($nomePerfil, 0, 1))) ?><?php endif; ?></div>
                <h2><?= site_escape($nomePerfil) ?></h2>
                <span class="conta-selo"><?= site_escape(['aluno' => 'Aluno', 'professor' => 'Professor', 'administrador' => 'Administrador'][$contaAtual['tipo_usuario']] ?? 'Usuário') ?></span>
                <p><?= site_escape($emailPerfil) ?></p>
                <a class="conta-primario" href="editar-perfil.php">Editar perfil</a>
                <a class="conta-secundario" href="configuracoes.php">Configurações</a>
            </aside>
            <div class="conta-detalhes">
                <section class="conta-cartao">
                    <div class="conta-titulo">
                        <div>
                            <h2>Informações pessoais</h2>
                            <p>Dados vinculados à sua conta.</p>
                        </div>
                    </div>
                    <dl class="conta-dados">
                        <div>
                            <dt>Nome completo</dt>
                            <dd><?= site_escape($nomePerfil) ?></dd>
                        </div>
                        <div>
                            <dt>E-mail</dt>
                            <dd><?= site_escape($emailPerfil) ?></dd>
                        </div>
                    </dl>
                </section>
                <section class="conta-cartao">
                    <section class="professor-area" aria-labelledby="professor-titulo">
                        <h2 id="professor-titulo"><span data-i18n="Acesso de professor">Acesso de professor</span></h2>
                        <?php if ($avisoPedido): ?><p class="pedido-aviso" role="status"><?= site_escape($avisoPedido) ?></p><?php endif; ?>
                        <?php if ($erroPedido): ?><p class="pedido-aviso" role="alert"><?= site_escape($erroPedido) ?></p><?php endif; ?>
                        <?php if ($contaAtual['tipo_usuario'] === 'professor'): ?>
                            <p>Sua conta já tem acesso de professor.</p>
                        <?php elseif ($contaAtual['tipo_usuario'] === 'administrador'): ?>
                            <p>Sua conta é administradora. Analise os pedidos no painel administrativo.</p>
                            <a class="pedido-botao" href="solicitacoes-professor.php"><span data-i18n="Ver solicitações">Ver solicitações</span></a>
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
                                <button class="pedido-botao" type="submit"><span data-i18n="Solicitar acesso de professor">Solicitar acesso de professor</span></button>
                            </form>
                        <?php endif; ?>
                    </section>
                </section>
            </div>
        </div>
    </div>
</main>
<?php require __DIR__ . '/includes/site/footer.php'; ?>