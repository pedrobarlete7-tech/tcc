<?php
declare(strict_types=1);

// Contadores compartilhados entre navegadores, sem senhas nem e-mails em texto claro.
function login_limite(string $email, string $ip, bool $sucesso = false): int
{
    $pasta = rtrim(sys_get_temp_dir(), '/\\') . '/ensinotec-login-' . substr(hash('sha256', __DIR__), 0, 24);
    if (!is_dir($pasta) && !@mkdir($pasta, 0700, true) && !is_dir($pasta))
        throw new RuntimeException('Não foi possível verificar as tentativas de acesso. Tente novamente mais tarde.');
    $arquivo = @fopen($pasta . '/tentativas.json', 'c+');
    if (!$arquivo) throw new RuntimeException('Não foi possível verificar as tentativas de acesso. Tente novamente mais tarde.');
    try {
        if (!flock($arquivo, LOCK_EX)) throw new RuntimeException('lock');
        $texto = stream_get_contents($arquivo);
        if ($texto === false) throw new RuntimeException('read');
        $dados = $texto === '' ? [] : json_decode($texto, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($dados)) throw new RuntimeException('format');
        $agora = time();
        foreach ($dados as $chave => $registro) {
            if (!is_array($registro) || !isset($registro['ate'], $registro['total'])) throw new RuntimeException('record');
            if ($registro['ate'] <= $agora) unset($dados[$chave]);
        }
        $conta = 'conta:' . hash('sha256', strtolower(trim($email)));
        $origem = 'ip:' . hash('sha256', $ip);
        $espera = 0;
        if ($sucesso) {
            unset($dados[$conta]);
        } else {
            foreach ([$conta => 5, $origem => 50] as $chave => $limite) {
                if (($dados[$chave]['total'] ?? 0) >= $limite)
                    $espera = max($espera, $dados[$chave]['ate'] - $agora);
            }
            if (!$espera) {
                if (count($dados) > 10000) throw new RuntimeException('capacity');
                foreach ([$conta, $origem] as $chave) {
                    if (!isset($dados[$chave])) $dados[$chave] = ['ate' => $agora + 300, 'total' => 0];
                    $dados[$chave]['total']++;
                }
            }
        }
        $novo = json_encode($dados, JSON_THROW_ON_ERROR);
        rewind($arquivo);
        if (fwrite($arquivo, $novo) !== strlen($novo) || !ftruncate($arquivo, strlen($novo)) || !fflush($arquivo))
            throw new RuntimeException('write');
        return $espera;
    } catch (Throwable $erro) {
        error_log('[EnsinoTec] Falha no armazenamento do limite de login.');
        throw new RuntimeException('Não foi possível verificar as tentativas de acesso. Tente novamente mais tarde.', 0, $erro);
    } finally {
        flock($arquivo, LOCK_UN);
        fclose($arquivo);
    }
}
