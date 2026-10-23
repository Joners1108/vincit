<?php
/**
 * Carrega variáveis de .env para getenv() / $_ENV (sem Composer).
 * Valores já definidos no sistema operativo têm prioridade.
 */
function vincit_load_env($path) {
    if (!is_readable($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || (isset($line[0]) && $line[0] === '#')) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if ($name === '') {
            continue;
        }
        $len = strlen($value);
        if ($len >= 2) {
            $q = $value[0];
            if (($q === '"' || $q === "'") && $value[$len - 1] === $q) {
                $value = substr($value, 1, -1);
            }
        }
        // Só respeita variável já definida no SO se tiver valor não vazio.
        // Caso contrário (ex.: STRIPE_SECRET_KEY="" no ambiente Windows), aplica o .env.
        $existing = getenv($name);
        if ($existing !== false && trim((string) $existing) !== '') {
            continue;
        }
        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
    }
}

/**
 * Carrega vários .env em sequência.
 * Variáveis já definidas (SO ou ficheiro anterior) não são sobrescritas.
 * Ordem: ficheiros pela ordem do array (primeiro definido ganha; os seguintes só preenchem chaves em falta).
 */
function vincit_load_env_files(array $paths) {
    foreach ($paths as $path) {
        vincit_load_env($path);
    }
}

/**
 * Lê uma chave directamente do .env (fallback quando getenv/putenv falham no Windows).
 *
 * @param string      $key  Nome da variável (ex.: STRIPE_SECRET_KEY)
 * @param string|null $path Caminho absoluto ao .env; por defeito site/.env
 */
function vincit_env_read_dotenv_key(string $key, ?string $path = null): string {
    if ($path === null) {
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
    }
    if (!is_readable($path)) {
        return '';
    }
    $raw = @file_get_contents($path);
    if ($raw === false || $raw === '') {
        return '';
    }
    if (str_starts_with($raw, "\xEF\xBB\xBF")) {
        $raw = substr($raw, 3);
    }
    $lines = preg_split('/\r\n|\r|\n/', $raw);
    $prefix = $key . '=';
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || (isset($line[0]) && $line[0] === '#')) {
            continue;
        }
        if (!str_starts_with($line, $prefix)) {
            continue;
        }
        $value = trim(substr($line, strlen($prefix)));
        $len = strlen($value);
        if ($len >= 2) {
            $q = $value[0];
            if (($q === '"' || $q === "'") && $value[$len - 1] === $q) {
                $value = substr($value, 1, -1);
            }
        }
        return $value;
    }
    return '';
}

function env($key, $default = '') {
    $v = getenv($key);
    if ($v === false && isset($_ENV[$key])) {
        $v = $_ENV[$key];
    }
    if ($v === false || $v === null) {
        return $default;
    }
    return $v;
}

function env_bool($key, $default = false) {
    $v = strtolower(trim((string) env($key, $default ? '1' : '0')));
    return in_array($v, ['1', 'true', 'yes', 'on'], true);
}

function env_float($key, $default = 0.0) {
    return (float) env($key, (string) $default);
}

function env_int($key, $default = 0) {
    return (int) env($key, (string) $default);
}
