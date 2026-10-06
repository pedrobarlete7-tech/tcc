<?php
// ALTERADO: permite enviar uma foto para a própria conta.
require_once __DIR__ . '/includes/site/auth.php';
header('Cache-Control: no-store');
if (!$autenticado) {
    header('Location: login.php', true, 303);
    exit;
}
$nome = $contaAtual['nome'];
$email = $contaAtual['email'];
$erro = '';
$aviso = $_SESSION['perfil_salvo'] ?? '';
unset($_SESSION['perfil_salvo']);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'foto') {
    try {
        if (!auth_csrf_valido()) {
            http_response_code(403);
            throw new DomainException('Solicitação expirada. Atualize a página.');
        }
        if (!seguranca_conta($pdo, (int)$contaAtual['id_usuario'])) throw new DomainException('Entre novamente para alterar a foto.');
        foto_perfil_salvar((int)$contaAtual['id_usuario'], is_array($_FILES['foto'] ?? null) ? $_FILES['foto'] : []);
        $_SESSION['perfil_salvo'] = 'Foto atualizada com sucesso';
        header('Location: editar-perfil.php', true, 303);
        exit;
    } catch (DomainException $e) {
        $erro = $e->getMessage();
    } catch (PDOException $e) {
        $erro = 'Não foi possível verificar sua conta agora.';
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') !== 'foto') {
    $nome = is_string($_POST['nome'] ?? null) ? trim($_POST['nome']) : '';
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $senha = is_string($_POST['senha'] ?? null) ? $_POST['senha'] : '';
    try {
        if (!auth_csrf_valido()) {
            http_response_code(403);
            throw new DomainException('Solicitação expirada. Atualize a página.');
        }
        if (mb_strlen($nome) < 2 || mb_strlen($nome) > 100) throw new DomainException('Informe um nome de 2 a 100 caracteres.');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) throw new DomainException('Informe um e-mail válido de até 100 caracteres.');
        if ((int)($_SESSION['perfil_bloqueado'] ?? 0) > time()) throw new DomainException('Aguarde alguns minutos antes de tentar novamente.');
        $pdo->beginTransaction();
        $id = (int)$contaAtual['id_usuario'];
        $q = $pdo->prepare('SELECT * FROM usuario WHERE id_usuario=? AND ativo=1 FOR UPDATE');
        $q->execute([$id]);
        $atual = $q->fetch(PDO::FETCH_ASSOC);
        if (!$atual || !password_verify($senha, $atual['senha'])) {
            $_SESSION['perfil_falhas'] = (int)($_SESSION['perfil_falhas'] ?? 0) + 1;
            if ($_SESSION['perfil_falhas'] >= 5) {
                $_SESSION['perfil_bloqueado'] = time() + 300;
                $_SESSION['perfil_falhas'] = 0;
            }
            throw new DomainException('Senha atual incorreta.');
        }
        $q = $pdo->prepare('SELECT id_usuario FROM usuario WHERE email=? AND id_usuario<>?');
        $q->execute([$email, $id]);
        if ($q->fetchColumn()) throw new DomainException('Este e-mail já está em uso por outra conta.');
        $q = $pdo->prepare('UPDATE usuario SET nome=?,email=? WHERE id_usuario=?');
        $q->execute([$nome, $email, $id]);
        if ($atual['email'] !== $email) {
            $q = $pdo->prepare('INSERT INTO usuario_seguranca (id_usuario,versao) VALUES (?,2) ON DUPLICATE KEY UPDATE versao=versao+1');
            $q->execute([$id]);
        }
        $contaNova = seguranca_conta($pdo, $id);
        $pdo->commit();
        unset($_SESSION['perfil_falhas'], $_SESSION['perfil_bloqueado']);
        auth_concluir($contaNova);
        $_SESSION['perfil_salvo'] = 'editado com sucesso';
        header('Location: editar-perfil.php', true, 303);
        exit;
    } catch (DomainException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $erro = $e->getMessage();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $erro = $e->getCode() === '23000' ? 'Este e-mail já está em uso.' : 'Não foi possível salvar agora. Tente novamente.';
    }
}
$tituloPagina = 'Editar perfil — EnsinoTec';
$estilosPagina = ['assets/css/perfil.css?v=foto-1', 'assets/css/editar-perfil.css?v=foto-1'];
require __DIR__ . '/includes/site/header.php';
?>
<main class="conta-pagina editar-perfil" id="conteudo-principal" tabindex="-1">
    <div class="conta-container">
        <a class="conta-voltar" href="perfil.php">← Voltar ao meu perfil</a>
        <header class="conta-cabecalho"><span class="conta-etiqueta">MINHA CONTA</span>
            <h1>Editar perfil</h1>
            <p>Mantenha suas informações atualizadas.</p>
        </header>
        <div class="conta-grade">
            <aside class="conta-identidade">
                <div class="conta-capa"></div>
                <div class="conta-avatar" aria-hidden="true"><?php if ($fotoUsuario): ?><img src="<?= site_escape($fotoUsuario) ?>" alt=""><?php else: ?><?= site_escape(mb_strtoupper(mb_substr($contaAtual['nome'], 0, 1))) ?><?php endif; ?></div>
                <h2><?= site_escape($contaAtual['nome']) ?></h2><span class="conta-selo"><?= site_escape(['aluno' => 'Aluno', 'professor' => 'Professor', 'administrador' => 'Administrador'][$contaAtual['tipo_usuario']] ?? 'Usuário') ?></span>
                <p><?= site_escape($contaAtual['email']) ?></p><a class="conta-secundario" href="perfil.php">Ver meu perfil</a>
                <form class="conta-foto-form" method="post" action="editar-perfil.php" enctype="multipart/form-data">
                    <input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>"><input type="hidden" name="acao" value="foto"><input type="hidden" name="MAX_FILE_SIZE" value="5242880">
                    <label for="foto-perfil">Foto do perfil</label><input id="foto-perfil" type="file" name="foto" accept="image/jpeg,image/png,image/webp" required aria-describedby="foto-ajuda">
                    <p id="foto-ajuda">JPG, PNG ou WebP · até 5 MB</p><button type="submit" class="conta-primario">Salvar foto</button>
                </form>
            </aside>
            <section class="conta-cartao">
                <div class="conta-titulo">
                    <div>
                        <h2>Informações pessoais</h2>
                        <p>Edite seus dados e confirme para salvar.</p>
                    </div>
                </div>
                <form method="post" action="editar-perfil.php">
                    <?php if ($erro): ?><p class="conta-erro" role="alert"><?= site_escape($erro) ?></p><?php endif; ?>
                    <input type="hidden" name="csrf" value="<?= site_escape(auth_token()) ?>">
                    <div class="conta-campo"><label for="perfil-nome">Nome completo</label><input id="perfil-nome" name="nome" autocomplete="name" required minlength="2" maxlength="100" value="<?= site_escape($nome) ?>"></div>
                    <div class="conta-campo"><label for="perfil-email">E-mail</label><input id="perfil-email" name="email" type="email" autocomplete="email" required maxlength="100" value="<?= site_escape($email) ?>"></div>
                    <div class="conta-seguranca">
                        <h3>Confirme sua identidade</h3>
                        <p id="ajuda-senha">Digite a senha que você usa para entrar no EnsinoTec.</p>
                        <div class="conta-campo"><label for="perfil-senha">Senha atual</label><input id="perfil-senha" name="senha" type="password" autocomplete="current-password" required aria-describedby="ajuda-senha" placeholder="Digite sua senha atual"></div>
                        <a class="conta-recuperar" href="recuperar-senha.php">Esqueceu sua senha?</a>
                    </div>
                    <div class="perfil-acoes"><a class="conta-secundario" href="perfil.php">Cancelar</a><button class="conta-primario" type="submit">Salvar alterações</button></div>
                </form>
            </section>
        </div>
    </div>
</main>
<?php $popupSucesso = $aviso;
require __DIR__ . '/includes/site/popup-sucesso.php';
require __DIR__ . '/includes/site/footer.php'; ?>