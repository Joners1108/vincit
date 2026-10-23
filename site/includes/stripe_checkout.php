<?php
/**
 * Stripe Checkout (sessão hospedada) — Vincit
 */
if (!defined('SITE_URL')) {
    return;
}

require_once __DIR__ . '/../vendor/autoload.php';

function vincit_stripe_configured(): bool {
    if (!function_exists('vincit_stripe_secret_key')) {
        return !empty(STRIPE_SECRET_KEY) && strlen(trim(STRIPE_SECRET_KEY)) > 10;
    }
    $sk = vincit_stripe_secret_key();
    return $sk !== '' && strlen(trim($sk)) > 10;
}

function vincit_stripe_init(): void {
    if (!vincit_stripe_configured()) {
        return;
    }
    \Stripe\Stripe::setApiKey(function_exists('vincit_stripe_secret_key') ? vincit_stripe_secret_key() : STRIPE_SECRET_KEY);
}

function vincit_create_stripe_checkout_session(
    int $orderId,
    string $customerEmail,
    array $lineItems,
    string $metadataUserId,
    string $metadataCartSessionId
): array {
    vincit_stripe_init();
    if (!vincit_stripe_configured()) {
        return ['success' => false, 'error' => 'Stripe não configurado'];
    }

    $successUrl = rtrim(SITE_URL, '/') . '/pagamento/sucesso?session_id={CHECKOUT_SESSION_ID}';
    $cancelUrl = rtrim(SITE_URL, '/') . '/pagamento/cancelar?order_id=' . $orderId;

    try {
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'customer_email' => $customerEmail,
            'metadata' => [
                'order_id' => (string) $orderId,
                'user_id' => $metadataUserId,
                'cart_session_id' => $metadataCartSessionId,
            ],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'billing_address_collection' => 'required',
            'shipping_address_collection' => [
                'allowed_countries' => ['PT', 'ES', 'FR', 'DE'],
            ],
        ]);

        return [
            'success' => true,
            'session_id' => $session->id,
            'url' => $session->url,
        ];
    } catch (\Throwable $e) {
        error_log('Vincit Stripe Checkout: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function vincit_verify_stripe_checkout_session(string $sessionId): array {
    vincit_stripe_init();
    try {
        $session = \Stripe\Checkout\Session::retrieve($sessionId);
        return [
            'success' => true,
            'session' => $session,
            'payment_status' => $session->payment_status,
        ];
    } catch (\Throwable $e) {
        error_log('Vincit Stripe verify: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function vincit_fulfill_stripe_order($session = null, ?string $sessionId = null): array {
    global $pdo;

    vincit_stripe_init();
    if ($session === null && $sessionId) {
        try {
            $session = \Stripe\Checkout\Session::retrieve($sessionId);
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    if (!$session instanceof \Stripe\Checkout\Session) {
        return ['ok' => false, 'error' => 'Sessão inválida'];
    }

    if ($session->payment_status !== 'paid') {
        return ['ok' => false, 'error' => 'Pagamento não concluído'];
    }

    $orderId = (int) ($session->metadata->order_id ?? 0);
    if ($orderId < 1) {
        return ['ok' => false, 'error' => 'Metadados em falta'];
    }

    $metaUser = (string) ($session->metadata->user_id ?? '');
    $userId = $metaUser !== '' ? (int) $metaUser : null;
    $cartSessionId = (string) ($session->metadata->cart_session_id ?? '');

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('SELECT id, payment_status, order_number FROM orders WHERE id = ? FOR UPDATE');
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => 'Encomenda não encontrada'];
        }

        if ($order['payment_status'] === 'paid') {
            $pdo->commit();
            return ['ok' => true, 'already' => true, 'order_id' => $orderId, 'order_number' => $order['order_number']];
        }

        require_once __DIR__ . '/product_sizes_lib.php';

        $itemsStmt = $pdo->prepare(
            'SELECT oi.product_id, oi.quantity, oi.product_size_id, ps.stock AS size_stock, p.stock AS product_stock
             FROM order_items oi
             INNER JOIN products p ON p.id = oi.product_id
             LEFT JOIN product_sizes ps ON ps.id = oi.product_size_id
             WHERE oi.order_id = ?'
        );
        $itemsStmt->execute([$orderId]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as $it) {
            $need = (int) $it['quantity'];
            if (!empty($it['product_size_id'])) {
                if ((int) ($it['size_stock'] ?? 0) < $need) {
                    $pdo->rollBack();
                    return ['ok' => false, 'error' => 'Stock insuficiente para concluir a encomenda'];
                }
            } elseif ((int) ($it['product_stock'] ?? 0) < $need) {
                $pdo->rollBack();
                return ['ok' => false, 'error' => 'Stock insuficiente para concluir a encomenda'];
            }
        }

        $syncPids = [];
        foreach ($items as $it) {
            $need = (int) $it['quantity'];
            if (!empty($it['product_size_id'])) {
                $u = $pdo->prepare('UPDATE product_sizes SET stock = stock - ? WHERE id = ? AND stock >= ?');
                $u->execute([$need, (int) $it['product_size_id'], $need]);
                if ($u->rowCount() === 0) {
                    $pdo->rollBack();
                    return ['ok' => false, 'error' => 'Stock insuficiente para concluir a encomenda'];
                }
                $syncPids[(int) $it['product_id']] = true;
            } else {
                $u = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
                $u->execute([$need, (int) $it['product_id'], $need]);
                if ($u->rowCount() === 0) {
                    $pdo->rollBack();
                    return ['ok' => false, 'error' => 'Stock insuficiente para concluir a encomenda'];
                }
            }
        }
        foreach (array_keys($syncPids) as $pid) {
            vincit_sync_product_size_string_and_total((int) $pid);
        }

        $pi = $session->payment_intent;
        if (is_object($pi) && isset($pi->id)) {
            $pi = $pi->id;
        }
        if (!is_string($pi) || $pi === '') {
            $pi = $session->id;
        }

        $u = $pdo->prepare(
            'UPDATE orders SET payment_status = ?, status = ?, stripe_payment_id = ?, stripe_checkout_session_id = ?, updated_at = NOW() WHERE id = ? AND payment_status = ?'
        );
        $u->execute(['paid', 'processing', $pi, $session->id, $orderId, 'pending']);

        if ($u->rowCount() === 0) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => 'Estado da encomenda inesperado'];
        }

        if ($userId) {
            $c = $pdo->prepare('DELETE FROM cart WHERE user_id = ?');
            $c->execute([$userId]);
        } elseif ($cartSessionId !== '') {
            $c = $pdo->prepare('DELETE FROM cart WHERE session_id = ?');
            $c->execute([$cartSessionId]);
        }

        $pdo->commit();

        $orderNumber = $order['order_number'];

        if (!function_exists('getOrderById')) {
            require_once __DIR__ . '/../database.php';
        }
        $fullOrder = getOrderById($orderId);
        $orderItems = getOrderItems($orderId);

        if ($fullOrder && !empty($fullOrder['shipping_email'])) {
            require_once __DIR__ . '/mailer.php';
            vincit_send_payment_confirmation_customer_email($fullOrder, $orderItems);
            vincit_send_new_order_paid_admin_email($fullOrder, $orderItems);
            require_once __DIR__ . '/admin_notifications_lib.php';
            vincit_admin_notify_insert(
                'Encomenda paga',
                'Pagamento confirmado para ' . $orderNumber . '.',
                rtrim(SITE_URL, '/') . '/admin/encomendas/editar/' . $orderId
            );
        }

        return ['ok' => true, 'order_id' => $orderId, 'order_number' => $orderNumber];
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Vincit fulfill: ' . $e->getMessage());
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}