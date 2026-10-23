<?php
/**
 * Favoritos — requer sessão
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../includes/favorites_lib.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Inicia sessão para usar favoritos.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode([
        'success' => true,
        'ids' => vincit_favorite_ids_for_user(),
    ]);
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $input['action'] ?? 'toggle';
$productId = (int) ($input['product_id'] ?? 0);

if ($productId < 1) {
    echo json_encode(['success' => false, 'message' => 'Produto inválido']);
    exit;
}

if ($action === 'remove') {
    $ok = vincit_remove_favorite($productId);
    echo json_encode(['success' => $ok, 'favorite' => false, 'ids' => vincit_favorite_ids_for_user()]);
    exit;
}

if ($action === 'add') {
    $ok = vincit_add_favorite($productId);
    echo json_encode(['success' => $ok, 'favorite' => true, 'ids' => vincit_favorite_ids_for_user()]);
    exit;
}

// toggle
if (vincit_is_favorite($productId)) {
    vincit_remove_favorite($productId);
    $fav = false;
} else {
    vincit_add_favorite($productId);
    $fav = true;
}

echo json_encode(['success' => true, 'favorite' => $fav, 'ids' => vincit_favorite_ids_for_user()]);
