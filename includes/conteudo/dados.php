<?php

declare(strict_types=1);
// ALTERADO: fornece IDs das alternativas para responder às questões.

function carregar_conteudo(PDO $pdo, int $id): ?array
{
    $consulta = $pdo->prepare(
        'SELECT c.id_conteudo, c.titulo, c.texto, c.nivel_dificuldade,
                m.titulo AS materia_titulo
         FROM conteudo c JOIN materia m ON m.id_materia = c.id_materia
         WHERE c.id_conteudo = ?'
    );
    $consulta->execute([$id]);
    $conteudo = $consulta->fetch(PDO::FETCH_ASSOC);
    if (!$conteudo) return null;

    $consultas = [
        'resumos' => 'SELECT id_resumo, caminho_imagem, descricao FROM resumo WHERE id_conteudo = ? ORDER BY id_resumo',
        'videos' => 'SELECT id_video, titulo, url_video FROM video WHERE id_conteudo = ? ORDER BY id_video',
        'exercicios' => 'SELECT e.id_exercicio, e.pergunta, EXISTS (SELECT 1 FROM alternativa a WHERE a.id_exercicio=e.id_exercicio AND a.correta=1) AS pode_responder FROM exercicio e WHERE e.id_conteudo = ? ORDER BY id_exercicio',
    ];
    foreach ($consultas as $chave => $sql) {
        $consulta = $pdo->prepare($sql);
        $consulta->execute([$id]);
        $conteudo[$chave] = $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    $consulta = $pdo->prepare(
        'SELECT a.id_exercicio, a.id_alternativa, a.texto FROM alternativa a
         JOIN exercicio e ON e.id_exercicio = a.id_exercicio
         WHERE e.id_conteudo = ? ORDER BY a.id_alternativa'
    );
    $consulta->execute([$id]);
    $alternativas = [];
    foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $linha) {
        $alternativas[$linha['id_exercicio']][] = $linha;
    }

    $consulta = $pdo->prepare(
        'SELECT i.id_exercicio, i.caminho_arquivo, i.legenda FROM imagem_exercicio i
         JOIN exercicio e ON e.id_exercicio = i.id_exercicio
         WHERE e.id_conteudo = ? ORDER BY i.ordem, i.id_imagem'
    );
    $consulta->execute([$id]);
    $imagens = [];
    foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $linha) {
        $imagens[$linha['id_exercicio']][] = $linha;
    }
    foreach ($conteudo['exercicios'] as &$exercicio) {
        $exercicio['alternativas'] = $alternativas[$exercicio['id_exercicio']] ?? [];
        $exercicio['imagens'] = $imagens[$exercicio['id_exercicio']] ?? [];
    }
    unset($exercicio);
    return $conteudo;
}

function url_midia(string $valor): ?string
{
    $valor = trim($valor);
    if ($valor === '' || preg_match('/[\x00-\x20]/', $valor)) return null;
    if (preg_match('~^https?://~i', $valor)) {
        return filter_var($valor, FILTER_VALIDATE_URL) ? $valor : null;
    }
    if (preg_match('~^[a-z][a-z0-9+.-]*:~i', $valor) || str_starts_with($valor, '//') || str_contains($valor, '\\')) return null;
    $caminho = rawurldecode(explode('?', explode('#', $valor)[0])[0]);
    $raiz = realpath(__DIR__ . '/../..');
    $arquivo = realpath($raiz . DIRECTORY_SEPARATOR . ltrim($caminho, '/'));
    if ($arquivo === false || !is_file($arquivo)) return null;
    $prefixo = strtolower($raiz . DIRECTORY_SEPARATOR);
    if (!str_starts_with(strtolower($arquivo), $prefixo)) return null;
    return ltrim($valor, '/');
}
