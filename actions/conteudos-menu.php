<?php
declare(strict_types=1);
// ALTERADO: carrega o menu sem exigir permissão de gravação na pasta de subdivisões.
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');
require_once __DIR__ . '/conexao.php';

try {
    $consulta = $pdo->query(
        'SELECT m.id_materia, m.titulo AS materia, c.id_conteudo, c.titulo
         FROM materia m
         LEFT JOIN conteudo c ON c.id_materia = m.id_materia
         ORDER BY m.titulo, m.id_materia, c.ordem, c.id_conteudo'
    );
    $registros = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $grupos = []; $vinculos = [];
    $caminho = __DIR__ . '/../storage/subdivisoes.json';
    if (is_file($caminho)) {
        $texto = @file_get_contents($caminho);
        $dados = $texto !== false ? json_decode($texto, true) : null;
        if (is_array($dados) && ($dados['versao'] ?? 0) === 1 && is_array($dados['grupos'] ?? null) && is_array($dados['conteudos'] ?? null)) {
            $grupos = $dados['grupos'];
            $vinculos = $dados['conteudos'];
        } else {
            error_log('[EnsinoTec] Menu: arquivo de subdivisões ilegível ou inválido; exibindo conteúdos diretamente nas matérias.');
        }
    }
    foreach ($registros as &$registro) {
        $idGrupo = $vinculos[(int)$registro['id_conteudo']] ?? null;
        $grupo = is_int($idGrupo) || is_string($idGrupo) ? ($grupos[$idGrupo] ?? null) : null;
        $valido = is_array($grupo) && (int)($grupo['id_materia'] ?? 0) === (int)$registro['id_materia'] && (int)($grupo['id_subdivisao'] ?? 0) > 0 && is_string($grupo['titulo'] ?? null);
        $registro['id_subdivisao'] = $valido ? (int)$grupo['id_subdivisao'] : null;
        $registro['subdivisao'] = $valido ? $grupo['titulo'] : null;
    }
    unset($registro);
    echo json_encode($registros, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
} catch (PDOException $erro) {
    http_response_code(503);
    $codigo = (int)($erro->errorInfo[1] ?? 0);
    error_log('[EnsinoTec] Menu: SQLSTATE ' . $erro->getCode() . '; código MySQL ' . $codigo);
    $mensagem = 'Não foi possível consultar as matérias e os conteúdos.';
    if ($codigo === 1146) $mensagem = 'Uma tabela necessária ao menu não existe neste banco. Confira a importação das tabelas materia e conteudo.';
    elseif ($codigo === 1054) $mensagem = 'A estrutura das tabelas do servidor é diferente da esperada pelo menu. Confira as colunas de materia e conteudo.';
    elseif (in_array($codigo, [1044, 1142], true)) $mensagem = 'O usuário do banco não tem permissão para consultar as tabelas do menu.';
    echo json_encode(['erro' => $mensagem, 'codigo' => $codigo], JSON_UNESCAPED_UNICODE);
} catch (JsonException $erro) {
    http_response_code(503);
    error_log('[EnsinoTec] Menu: falha ao gerar JSON.');
    echo json_encode(['erro' => 'Não foi possível preparar os dados do menu.']);
}
