<?php
/**
 * Popular a base de dados com produtos de demonstração (e imagens em assets/img/products/).
 *
 * Linha de comando: php seed_demo_products.php
 * Browser (só com DEBUG_MODE): /site/seed_demo_products.php?run=1
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/includes/demo_products.php';

$isCli = php_sapi_name() === 'cli';

if (!$isCli) {
    header('Content-Type: text/plain; charset=utf-8');
    if (!defined('DEBUG_MODE') || !DEBUG_MODE) {
        http_response_code(403);
        echo 'Acesso negado.';
        exit;
    }
    if (!isset($_GET['run']) || $_GET['run'] !== '1') {
        echo 'Abre este URL com ?run=1 (apenas em desenvolvimento).';
        exit;
    }
}

try {
    $result = seedDemoProducts($pdo);
    $msg = "Inseridos: {$result['inserted']}, já existentes (mesmo slug): {$result['skipped']}";
    if ($isCli) {
        echo $msg . PHP_EOL;
    } else {
        echo $msg;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Erro: ' . $e->getMessage();
}
