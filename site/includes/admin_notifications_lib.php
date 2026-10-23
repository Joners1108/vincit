<?php
/**
 * Notificações no painel de admin (tabela admin_notifications).
 */
function vincit_admin_notify_insert(string $title, string $body, ?string $link = null): void {
    global $pdo;
    if (!isset($pdo)) {
        return;
    }
    try {
        $st = $pdo->prepare('INSERT INTO admin_notifications (title, body, link, is_read) VALUES (?, ?, ?, 0)');
        $st->execute([$title, $body, $link]);
    } catch (Throwable $e) {
        error_log('admin_notifications: ' . $e->getMessage());
    }
}

function vincit_admin_notify_unread_count(): int {
    global $pdo;
    if (!isset($pdo)) {
        return 0;
    }
    try {
        return (int) $pdo->query('SELECT COUNT(*) FROM admin_notifications WHERE is_read = 0')->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

function vincit_admin_notify_recent(int $limit = 15): array {
    global $pdo;
    if (!isset($pdo)) {
        return [];
    }
    try {
        $lim = max(1, min(50, $limit));
        return $pdo->query('SELECT * FROM admin_notifications ORDER BY created_at DESC LIMIT ' . $lim)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        return [];
    }
}

function vincit_admin_notify_mark_read(int $id): void {
    global $pdo;
    if (!isset($pdo)) {
        return;
    }
    $st = $pdo->prepare('UPDATE admin_notifications SET is_read = 1 WHERE id = ?');
    $st->execute([$id]);
}

function vincit_admin_notify_mark_all_read(): void {
    global $pdo;
    if (!isset($pdo)) {
        return;
    }
    $pdo->exec('UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0');
}
