<?php
/**
 * Detalhe de uma encomenda (área de cliente)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';

$pageTitle = 'Detalhe da encomenda';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/login?redirect=' . urlencode(SITE_URL . '/conta'));
}

$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($orderId < 1) {
    redirect(SITE_URL . '/conta');
}

$currentUser = getCurrentUser();
if (!$currentUser) {
    redirect(SITE_URL . '/login');
}

$order = dbFetch('SELECT * FROM orders WHERE id = ? AND user_id = ?', [$orderId, $currentUser['id']]);
if (!$order) {
    $_SESSION['notification'] = [
        'type' => 'error',
        'message' => 'Encomenda não encontrada.',
    ];
    redirect(SITE_URL . '/conta');
}

$pageTitle = 'Encomenda ' . $order['order_number'];
$orderItems = getOrderItems($orderId);

$statuses = [
    'pending' => 'Pendente',
    'processing' => 'Em processamento',
    'shipped' => 'Enviada',
    'delivered' => 'Entregue',
    'cancelled' => 'Cancelada',
];
$paymentStatuses = [
    'pending' => 'Pendente',
    'paid' => 'Pago',
    'failed' => 'Falhou',
    'refunded' => 'Reembolsado',
];

$tracking = trim((string) ($order['tracking_code'] ?? ''));

include_once __DIR__ . '/../includes/header.php';
?>

<section class="breadcrumb">
    <div class="container">
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb-list">
                <li><a href="<?php echo SITE_URL; ?>">Início</a></li>
                <li><a href="<?php echo SITE_URL; ?>/conta">Minha conta</a></li>
                <li><?php echo htmlspecialchars($order['order_number']); ?></li>
            </ol>
        </nav>
    </div>
</section>

<section class="account-section">
    <div class="container">
        <div class="account-order-toolbar" style="display:flex;flex-wrap:wrap;gap:0.75rem;align-items:center;margin-bottom:1rem;">
            <a href="<?php echo SITE_URL; ?>/conta" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Voltar à conta</a>
            <button type="button" class="btn btn-outline btn-sm" onclick="window.print();"><i class="fas fa-print"></i> Imprimir</button>
        </div>

        <h1 class="page-title">Encomenda <?php echo htmlspecialchars($order['order_number']); ?></h1>
        <p class="text-muted"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>

        <div id="account-order-print" class="order-detail-customer" style="display:grid;gap:1.5rem;margin-top:1.5rem;">

            <div class="card-like" style="background:var(--surface-2);padding:1.25rem;border-radius:var(--radius-lg);border:1px solid var(--border-light);">
                <h2 style="margin:0 0 1rem;font-size:1.1rem;">Estado e pagamento</h2>
                <p style="margin:0 0 0.5rem;">
                    <span class="status-badge status-<?php echo htmlspecialchars($order['status']); ?>"><?php echo htmlspecialchars($statuses[$order['status']] ?? $order['status']); ?></span>
                </p>
                <p style="margin:0.35rem 0;"><strong>Estado do pagamento:</strong> <?php echo htmlspecialchars($paymentStatuses[$order['payment_status']] ?? $order['payment_status']); ?></p>
                <p style="margin:0.35rem 0;"><strong>Método de pagamento:</strong> <?php echo htmlspecialchars(vincit_format_order_payment_method($order)); ?></p>
                <p style="margin:0.35rem 0;"><strong>Total:</strong> <?php echo formatPrice($order['total']); ?></p>
                <?php if ($tracking !== ''): ?>
                <p style="margin:0.75rem 0 0;">
                    <strong>Código de rastreio:</strong>
                    <?php echo htmlspecialchars($tracking); ?>
                    <a href="https://www.ctt.pt/particulares/rastreamento/objectos?objects=<?php echo rawurlencode($tracking); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-sm" style="margin-left:0.5rem;vertical-align:middle;">Rastrear (CTT)</a>
                </p>
                <?php elseif (in_array($order['status'], ['shipped', 'delivered'], true)): ?>
                <p style="margin:0.75rem 0 0;font-size:0.9rem;color:var(--text-muted);">Ainda não há código de rastreio associado. Contacta o apoio se precisares de ajuda.</p>
                <?php endif; ?>
            </div>

            <div class="card-like" style="background:var(--surface-2);padding:1.25rem;border-radius:var(--radius-lg);border:1px solid var(--border-light);">
                <h2 style="margin:0 0 1rem;font-size:1.1rem;">Morada de envio</h2>
                <p style="margin:0;line-height:1.65;">
                    <?php echo htmlspecialchars($order['shipping_name'] ?? ''); ?><br>
                    <?php if (!empty($order['shipping_phone'])): ?><?php echo htmlspecialchars($order['shipping_phone']); ?><br><?php endif; ?>
                    <?php echo nl2br(htmlspecialchars((string) ($order['shipping_address'] ?? ''))); ?><br>
                    <?php echo htmlspecialchars(trim(($order['shipping_postal_code'] ?? '') . ' ' . ($order['shipping_city'] ?? ''))); ?><br>
                    <?php echo htmlspecialchars((string) ($order['shipping_country'] ?? '')); ?><br>
                    <span style="color:var(--text-muted);"><?php echo htmlspecialchars((string) ($order['shipping_email'] ?? '')); ?></span>
                </p>
            </div>

            <div class="card-like" style="background:var(--surface-2);padding:1.25rem;border-radius:var(--radius-lg);border:1px solid var(--border-light);">
                <h2 style="margin:0 0 1rem;font-size:1.1rem;">Artigos</h2>
                <div style="overflow-x:auto;">
                    <table class="account-order-items-table" style="width:100%;border-collapse:collapse;font-size:0.95rem;">
                        <thead>
                            <tr style="border-bottom:2px solid var(--border-light);text-align:left;">
                                <th style="padding:0.5rem 0.35rem;">Produto</th>
                                <th style="padding:0.5rem 0.35rem;text-align:center;">Qtd</th>
                                <th style="padding:0.5rem 0.35rem;text-align:right;">Preço</th>
                                <th style="padding:0.5rem 0.35rem;text-align:right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $oi): ?>
                            <tr style="border-bottom:1px solid var(--border-light);">
                                <td style="padding:0.65rem 0.35rem;">
                                    <div style="display:flex;align-items:center;gap:0.65rem;">
                                        <?php if (!empty($oi['image'])): ?>
                                        <img src="<?php echo htmlspecialchars(vincit_product_image_url($oi['image'])); ?>" alt="" width="56" height="56" style="object-fit:cover;border-radius:6px;flex-shrink:0;">
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($oi['product_name']); ?><?php if (!empty($oi['size_label'])): ?> <small style="color:#666;">(<?php echo htmlspecialchars($oi['size_label']); ?>)</small><?php endif; ?></span>
                                    </div>
                                </td>
                                <td style="padding:0.65rem 0.35rem;text-align:center;"><?php echo (int) $oi['quantity']; ?></td>
                                <td style="padding:0.65rem 0.35rem;text-align:right;white-space:nowrap;"><?php echo formatPrice($oi['price']); ?></td>
                                <td style="padding:0.65rem 0.35rem;text-align:right;white-space:nowrap;"><?php echo formatPrice($oi['total']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="padding:0.65rem 0.35rem;text-align:right;"><strong>Subtotal</strong></td>
                                <td style="padding:0.65rem 0.35rem;text-align:right;white-space:nowrap;"><?php echo formatPrice($order['subtotal']); ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="padding:0.65rem 0.35rem;text-align:right;"><strong>Portes</strong></td>
                                <td style="padding:0.65rem 0.35rem;text-align:right;white-space:nowrap;"><?php echo formatPrice($order['shipping_cost']); ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="padding:0.65rem 0.35rem;text-align:right;"><strong>Total</strong></td>
                                <td style="padding:0.65rem 0.35rem;text-align:right;white-space:nowrap;font-weight:700;"><?php echo formatPrice($order['total']); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <?php if (!empty($order['notes'])): ?>
            <div class="card-like" style="background:var(--surface-2);padding:1.25rem;border-radius:var(--radius-lg);border:1px solid var(--border-light);">
                <h2 style="margin:0 0 1rem;font-size:1.1rem;">Notas do pedido</h2>
                <p style="margin:0;white-space:pre-wrap;"><?php echo htmlspecialchars((string) $order['notes']); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
@media print {
    .main-header, .main-footer, .breadcrumb, .account-order-toolbar, .mobile-menu, .cart-icon { display: none !important; }
    body { padding-top: 0 !important; background: #fff !important; }
    .main-content { padding-top: 0 !important; }
}
</style>

<?php
include_once __DIR__ . '/../includes/footer.php';
