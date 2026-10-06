<?php

declare(strict_types=1);
// NOVO: guarda subdivisões em JSON com bloqueio de gravação e troca atômica do arquivo.
final class SubdivisoesArquivo
{
    private $trava;
    private string $caminho;
    public array $dados;
    public function __construct(PDO $pdo)
    {
        $pasta = __DIR__ . '/../../storage';
        if (!is_dir($pasta) && !@mkdir($pasta, 0755, true) && !is_dir($pasta)) throw new DomainException('Não foi possível criar a pasta storage. Confira a permissão de gravação.');
        $this->caminho = $pasta . '/subdivisoes.json';
        $this->trava = @fopen($pasta . '/subdivisoes.lock', 'c');
        if (!$this->trava || !flock($this->trava, LOCK_EX)) throw new DomainException('Não foi possível abrir as subdivisões. Confira a permissão da pasta storage.');
        if (is_file($this->caminho)) {
            try {
                $dados = json_decode((string)file_get_contents($this->caminho), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new DomainException('O arquivo de subdivisões está inválido. Restaure o backup antes de continuar.');
            }
            if (!is_array($dados) || ($dados['versao'] ?? 0) !== 1 || !is_array($dados['grupos'] ?? null) || !is_array($dados['conteudos'] ?? null) || !is_int($dados['proximo'] ?? null)) throw new DomainException('Formato inválido no arquivo de subdivisões.');
            $this->dados = $dados;
        } else {
            $this->dados = ['versao' => 1, 'proximo' => 1, 'grupos' => [], 'conteudos' => []];
            $mapa = ['Números e operações' => ['Frações', 'Números Decimais', 'Números naturais', 'Porcentagem', 'Razão e Proporção'], 'Geometria' => ['Área e Perímetro', 'Teorema de Pitágoras'], 'Álgebra' => ['Equações do 1º Grau', 'Equações do 2º Grau', 'Expressões Algébricas'], 'Probabilidade' => ['Probabilidade']];
            $q = $pdo->query("SELECT id_materia FROM materia WHERE titulo='Matemática' ORDER BY id_materia");
            foreach ($q->fetchAll(PDO::FETCH_COLUMN) as $materia) {
                $c = $pdo->prepare('SELECT id_conteudo,titulo FROM conteudo WHERE id_materia=?');
                $c->execute([$materia]);
                $conteudos = $c->fetchAll(PDO::FETCH_ASSOC);
                foreach ($mapa as $titulo => $titulos) {
                    $id = $this->dados['proximo']++;
                    $this->dados['grupos'][$id] = ['id_subdivisao' => $id, 'id_materia' => (int)$materia, 'titulo' => $titulo];
                    foreach ($conteudos as $item) foreach ($titulos as $nome) if (mb_strtolower($item['titulo']) === mb_strtolower($nome)) $this->dados['conteudos'][(int)$item['id_conteudo']] = $id;
                }
            }
            $this->gravar($this->dados);
        }
    }
    public function lista(): array
    {
        $lista = array_values($this->dados['grupos']);
        usort($lista, fn($a, $b) => strnatcasecmp($a['titulo'], $b['titulo']));
        return $lista;
    }
    public function grupo(int $conteudo, int $materia): ?array
    {
        $id = $this->dados['conteudos'][$conteudo] ?? 0;
        $grupo = $this->dados['grupos'][$id] ?? null;
        return $grupo && (int)$grupo['id_materia'] === $materia ? $grupo : null;
    }
    public function vincular(int $conteudo, int $materia, int $grupo): void
    {
        if ($grupo && (int)($this->dados['grupos'][$grupo]['id_materia'] ?? 0) !== $materia) throw new DomainException('Escolha uma subdivisão pertencente à matéria selecionada.');
        if ($grupo) $this->dados['conteudos'][$conteudo] = $grupo;
        else unset($this->dados['conteudos'][$conteudo]);
    }
    private function gravar(array $dados): void
    {
        $json = json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $tmp = tempnam(dirname($this->caminho), 'sub-');
        if (!$tmp) throw new DomainException('Não foi possível preparar o arquivo de subdivisões.');
        try {
            if (file_put_contents($tmp, $json) !== strlen($json) || !@rename($tmp, $this->caminho)) throw new DomainException('Não foi possível salvar as subdivisões. Confira a permissão da pasta storage.');
        } finally {
            if (is_file($tmp)) unlink($tmp);
        }
    }
    public function confirmar(PDO $pdo): void
    {
        $anterior = json_decode((string)file_get_contents($this->caminho), true, 512, JSON_THROW_ON_ERROR);
        $this->gravar($this->dados);
        try {
            $pdo->commit();
        } catch (Throwable $e) {
            $this->gravar($anterior);
            throw $e;
        }
    }
    public function __destruct()
    {
        if (is_resource($this->trava)) {
            flock($this->trava, LOCK_UN);
            fclose($this->trava);
        }
    }
}
