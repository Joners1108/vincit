<?php
/**
 * Submissão de avaliações (formulário na ficha de produto)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit('Método não permitido');
}

$productId = (int) ($_POST['product_id'] ?? 0);
$slug = trim((string) ($_POST['product_slug'] ?? ''));
$rating = (int) ($_POST['rating'] ?? 0);
$comment = trim((string) ($_POST['comment'] ?? ''));
$token = $_POST['csrf_token'] ?? '';

if (!verifyCSRFToken($token)) {
    $_SESSION['notification'] = ['type' => 'error', 'message' => 'Sessão expirou. Tenta novamente.'];
    vincit_reviews_redirect($slug, $productId);
}

if (!isLoggedIn()) {
    $_SESSION['notification'] = ['type' => 'error', 'message' => 'Inicia sessão para avaliar.'];
    vincit_reviews_redirect($slug, $productId);
}

if ($productId < 1 || $rating < 1 || $rating > 5 || $comment === '') {
    $_SESSION['notification'] = ['type' => 'error', 'message' => 'Preenche classificação e comentário.'];
    vincit_reviews_redirect($slug, $productId);
}

$product = getProductById($productId);
if (!$product) {
    $_SESSION['notification'] = ['type' => 'error', 'message' => 'Produto não encontrado.'];
    redirect(SITE_URL . '/loja');
}

$uid = (int) $_SESSION['user_id'];
$already = dbFetch('SELECT id FROM reviews WHERE product_id = ? AND user_id = ? LIMIT 1', [$productId, $uid]);
if ($already) {
    $_SESSION['notification'] = ['type' => 'info', 'message' => 'Já deixaste uma avaliação neste produto.'];
    vincit_reviews_redirect($slug ?: ($product['slug'] ?? ''), $productId);
}

$rid = dbInsert('reviews', [
    'product_id' => $productId,
    'user_id' => $uid,
    'rating' => $rating,
    'comment' => $comment,
    'status' => 'approved',
]);
if ($rid) {
    $_SESSION['notification'] = [
        'type' => 'success',
        'message' => 'Obrigado! A tua avaliação foi publicada.',
    ];
} else {
    $_SESSION['notification'] = ['type' => 'error', 'message' => 'Não foi possível guardar a avaliação. Tenta mais tarde.'];
}

vincit_reviews_redirect($slug ?: ($product['slug'] ?? ''), $productId);

function vincit_reviews_redirect($slug, $productId) {
    $hash = '#reviews';
    if ($slug !== '') {
        redirect(SITE_URL . '/produto/' . rawurlencode($slug) . $hash);
    }
    $p = getProductById((int) $productId);
    if ($p && !empty($p['slug'])) {
        redirect(SITE_URL . '/produto/' . rawurlencode($p['slug']) . $hash);
    }
    redirect(SITE_URL . '/loja');
}
