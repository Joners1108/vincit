<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../includes/stripe_checkout.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$payload = file_get_contents('php://input');
$sig = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

$whSecret = function_exists('vincit_stripe_webhook_secret') ? vincit_stripe_webhook_secret() : STRIPE_WEBHOOK_SECRET;
if ($whSecret === '' || strlen(trim($whSecret)) < 10) {
    http_response_code(500);
    echo json_encode(['error' => 'Webhook não configurado']);
    exit;
}

vincit_stripe_init();

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig, $whSecret);
} catch (\Throwable $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Assinatura inválida']);
    exit;
}

if ($event->type === 'checkout.session.completed') {
    $session = $event->data->object;
    $sid = is_string($session) ? $session : $session->id;
    $full = \Stripe\Checkout\Session::retrieve($sid);
    $res = vincit_fulfill_stripe_order($full, null);
    echo json_encode(['received' => true, 'fulfill' => $res['ok'] ?? false]);
    exit;
}

echo json_encode(['received' => true, 'ignored' => $event->type]);
