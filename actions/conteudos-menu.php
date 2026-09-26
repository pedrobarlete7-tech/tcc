<?php
declare(strict_types=1);

// NOVO: fornece ao menu os IDs reais dos conteúdos cadastrados no banco.
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');
require_once __DIR__ . '/conexao.php';

try {
    $consulta = $pdo->query(
        'SELECT m.id_materia, m.titulo AS materia,
                c.id_conteudo, c.titulo
         FROM materia m
         LEFT JOIN conteudo c ON c.id_materia = m.id_materia
         ORDER BY m.titulo, m.id_materia, c.ordem, c.id_conteudo'
    );
    echo json_encode($consulta->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (PDOException | JsonException $erro) {
    http_response_code(503);
    error_log('[EnsinoTec] Falha no menu. Código: ' . $erro->getCode());
    echo json_encode(['erro' => 'Conteúdos temporariamente indisponíveis.']);
}
