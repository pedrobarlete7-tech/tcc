<?php

declare(strict_types=1);

// ALTERADO: combina os conteúdos do banco com as subdivisões do JSON.
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/../includes/conteudo/subdivisoes.php';

try {
    $arquivoSubdivisoes = new SubdivisoesArquivo($pdo);
    $consulta = $pdo->query(
        'SELECT m.id_materia, m.titulo AS materia,
                c.id_conteudo, c.titulo
         FROM materia m
         LEFT JOIN conteudo c ON c.id_materia = m.id_materia
         ORDER BY m.titulo, m.id_materia, c.ordem, c.id_conteudo'
    );
    $registros = $consulta->fetchAll(PDO::FETCH_ASSOC);
    foreach ($registros as &$registro) {
        $grupo = $arquivoSubdivisoes->grupo((int)$registro['id_conteudo'], (int)$registro['id_materia']);
        $registro['id_subdivisao'] = $grupo['id_subdivisao'] ?? null;
        $registro['subdivisao'] = $grupo['titulo'] ?? null;
    }
    unset($registro);
    echo json_encode($registros, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (PDOException | JsonException | DomainException $erro) {
    http_response_code(503);
    error_log('[EnsinoTec] Falha no menu. Código: ' . $erro->getCode());
    echo json_encode(['erro' => 'Conteúdos temporariamente indisponíveis.']);
}
