<?php
/**
 * Atualiza o esquema da base de dados Vincit (idempotente).
 * Na web, as mesmas migrações correm automaticamente ao carregar database.php.
 *
 * Uso (CLI): php site/update_database.php
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    header('HTTP/1.1 403 Forbidden');
    echo 'Este script só pode ser executado em linha de comandos: php site/update_database.php';
    exit(1);
}

define('VINCIT_CLI_SCHEMA_UPDATE', true);
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/includes/schema_migrate.php';

$log = static function (string $line): void {
    fwrite(STDOUT, $line . "\n");
};

$log('');
$log('=== Vincit — atualização da base de dados ===');
$log('Base: ' . DB_NAME . ' @ ' . DB_HOST);
$log('');

try {
    vincit_apply_schema_migrations($pdo, $log);
    vincit_schema_revision_persist($pdo);
    exit(0);
} catch (Throwable $e) {
    $log('');
    $log('ERRO: ' . $e->getMessage());
    $log('');
    exit(1);
}
