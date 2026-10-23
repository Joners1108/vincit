<?php
/**
 * Favoritos (utilizador autenticado)
 */
function vincit_favorite_ids_for_user(): array {
    global $pdo;
    if (!isLoggedIn()) {
        return [];
    }
    $st = $pdo->prepare('SELECT product_id FROM favorites WHERE user_id = ?');
    $st->execute([(int) $_SESSION['user_id']]);
    return array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
}

function vincit_is_favorite(int $productId): bool {
    global $pdo;
    if (!isLoggedIn() || $productId < 1) {
        return false;
    }
    $st = $pdo->prepare('SELECT 1 FROM favorites WHERE user_id = ? AND product_id = ? LIMIT 1');
    $st->execute([(int) $_SESSION['user_id'], $productId]);
    return (bool) $st->fetchColumn();
}

function vincit_add_favorite(int $productId): bool {
    global $pdo;
    if (!isLoggedIn() || $productId < 1) {
        return false;
    }
    $p = getProductById($productId);
    if (!$p) {
        return false;
    }
    try {
        $st = $pdo->prepare('INSERT IGNORE INTO favorites (user_id, product_id) VALUES (?, ?)');
        return $st->execute([(int) $_SESSION['user_id'], $productId]);
    } catch (Throwable $e) {
        error_log('favorites add: ' . $e->getMessage());
        return false;
    }
}

function vincit_remove_favorite(int $productId): bool {
    global $pdo;
    if (!isLoggedIn() || $productId < 1) {
        return false;
    }
    $st = $pdo->prepare('DELETE FROM favorites WHERE user_id = ? AND product_id = ?');
    return $st->execute([(int) $_SESSION['user_id'], $productId]);
}
