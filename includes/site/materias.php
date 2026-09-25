<?php
declare(strict_types=1);

// NOVO: reutiliza a conexão configurada para o XAMPP.
require_once __DIR__ . '/../../actions/conexao.php';

$materias = [];
$erroMaterias = false;

try {

    $consultaMaterias = $pdo->query(
        'SELECT m.id_materia, m.titulo AS materia_titulo,
                c.id_conteudo, c.titulo AS conteudo_titulo
         FROM materia m
         LEFT JOIN conteudo c ON c.id_materia = m.id_materia
         ORDER BY m.titulo, m.id_materia, c.ordem, c.titulo, c.id_conteudo'
    );

    // NOVO: agrupa os conteúdos dentro de cada matéria.
    foreach ($consultaMaterias as $linhaMateria) {
        $idMateria = (int) $linhaMateria['id_materia'];
        if (!isset($materias[$idMateria])) {
            $materias[$idMateria] = [
                'titulo' => (string) $linhaMateria['materia_titulo'],
                'conteudos' => [],
            ];
        }
        if ($linhaMateria['id_conteudo'] !== null) {
            $materias[$idMateria]['conteudos'][] = [
                'titulo' => (string) $linhaMateria['conteudo_titulo'],
                'url' => 'conteudo.php?id_conteudo=' . (int) $linhaMateria['id_conteudo'],
            ];
        }
    }
} catch (PDOException $erroConsulta) {
    // NOVO: exibe aviso no menu sem expor detalhes internos da consulta.
    $materias = [];
    $erroMaterias = true;
    error_log('[EnsinoTec] Falha ao listar matérias. Código: ' . $erroConsulta->getCode());
}
