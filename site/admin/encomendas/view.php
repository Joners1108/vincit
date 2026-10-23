<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Visualizar Encomenda - Admin
 */

// Incluir ficheiros de configuração e base de dados
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';

// Verificar se está logado e é admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login');
}

// Obter ID da encomenda (rewrite mete ?id=; sem rewrite, extrair do path)
$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($orderId <= 0 && !empty($_SERVER['REQUEST_URI'])) {
    if (preg_match('#/admin/encomendas/(?:editar/)?([0-9]+)(?:/|\?|#|$)#', (string) $_SERVER['REQUEST_URI'], $m)) {
        $orderId = (int) $m[1];
    }
}
if ($orderId <= 0) {
    redirect(SITE_URL . '/admin/encomendas');
}

// Obter encomenda
$order = getOrderById($orderId);

if (!$order) {
    $_SESSION['notification'] = [
        'type' => 'error',
        'message' => 'Encomenda não encontrada'
    ];
    redirect(SITE_URL . '/admin/encomendas');
}

// Obter itens da encomenda
$orderItems = getOrderItems($orderId);

// Obter utilizador
$user = null;
if ($order['user_id']) {
    $user = dbFetch("SELECT * FROM users WHERE id = ?", [$order['user_id']]);
}

// Definir título da página
$pageTitle = 'Encomenda #' . $order['order_number'];

// Processar atualização de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Validar CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Token de segurança inválido'
        ];
        redirect(SITE_URL . '/admin/encomendas/editar/' . $orderId);
    }
    
    $action = $_POST['action'];
    
    if ($action === 'update_status') {
        $status = $_POST['status'] ?? '';
        $paymentStatus = $_POST['payment_status'] ?? '';
        $oldStatus = $order['status'];

        dbUpdate('orders', [
            'status' => $status,
            'payment_status' => $paymentStatus,
        ], 'id = ?', [$orderId]);

        if ($oldStatus !== $status) {
            require_once __DIR__ . '/../../includes/mailer.php';
            $updatedOrder = getOrderById($orderId);
            if ($updatedOrder) {
                $itemsMail = getOrderItems($orderId);
                vincit_send_order_status_email_to_customer($updatedOrder, $status, $itemsMail);
            }
        }

        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Estado da encomenda atualizado!',
        ];

        redirect(SITE_URL . '/admin/encomendas/editar/' . $orderId);
    }

    if ($action === 'update_tracking') {
        $tracking = trim((string) ($_POST['tracking_code'] ?? ''));
        if (strlen($tracking) > 160) {
            $tracking = substr($tracking, 0, 160);
        }
        dbUpdate('orders', [
            'tracking_code' => $tracking !== '' ? $tracking : null,
        ], 'id = ?', [$orderId]);

        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Código de rastreio atualizado.',
        ];
        redirect(SITE_URL . '/admin/encomendas/editar/' . $orderId);
    }
}

// Incluir header do admin
include_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Order View Content -->
<div class="admin-content">
    <div class="admin-header">
        <div class="header-title">
            <h1>Encomenda #<?php echo $order['order_number']; ?></h1>
            <p>Detalhes da encomenda</p>
        </div>
        <div class="header-actions">
            <button type="button" class="btn btn-outline" onclick="window.print();">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <a href="<?php echo SITE_URL; ?>/admin/encomendas" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
    
    <div class="order-view-grid" id="admin-order-print-root">
        <!-- Order Details -->
        <div class="order-details-card">
            <div class="card-header">
                <h2>Detalhes da Encomenda</h2>
            </div>
            <div class="card-content">
                <div class="order-info">
                    <div class="info-row">
                        <span class="info-label">Número da Encomenda:</span>
                        <span class="info-value"><?php echo $order['order_number']; ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Data:</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Estado:</span>
                        <span class="info-value">
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php 
                                $statuses = [
                                    'pending' => 'Pendente',
                                    'processing' => 'Em processamento',
                                    'shipped' => 'Enviado',
                                    'delivered' => 'Entregue',
                                    'cancelled' => 'Cancelado'
                                ];
                                echo $statuses[$order['status']] ?? $order['status'];
                                ?>
                            </span>
                        </span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Estado de Pagamento:</span>
                        <span class="info-value">
                            <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                                <?php 
                                $paymentStatuses = [
                                    'pending' => 'Pendente',
                                    'paid' => 'Pago',
                                    'failed' => 'Falhou',
                                    'refunded' => 'Reembolsado'
                                ];
                                echo $paymentStatuses[$order['payment_status']] ?? $order['payment_status'];
                                ?>
                            </span>
                        </span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Método de Pagamento:</span>
                        <span class="info-value"><?php echo htmlspecialchars(vincit_format_order_payment_method($order)); ?></span>
                    </div>
                    <?php
                    $tc = trim((string) ($order['tracking_code'] ?? ''));
                    ?>
                    <div class="info-row">
                        <span class="info-label">Rastreio (CTT / transportadora):</span>
                        <span class="info-value">
                            <?php if ($tc !== ''): ?>
                                <?php echo htmlspecialchars($tc); ?>
                                <a href="https://www.ctt.pt/particulares/rastreamento/objectos?objects=<?php echo rawurlencode($tc); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline" style="margin-left:0.5rem;vertical-align:middle;">Rastrear (CTT)</a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <?php if ($order['stripe_payment_id']): ?>
                    <div class="info-row">
                        <span class="info-label">ID Stripe:</span>
                        <span class="info-value"><?php echo $order['stripe_payment_id']; ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Customer Details -->
        <div class="customer-details-card">
            <div class="card-header">
                <h2>Dados do Cliente</h2>
            </div>
            <div class="card-content">
                <div class="customer-info">
                    <div class="info-row">
                        <span class="info-label">Nome:</span>
                        <span class="info-value"><?php echo $order['shipping_name']; ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo $order['shipping_email']; ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Telefone:</span>
                        <span class="info-value"><?php echo $order['shipping_phone'] ?? '-'; ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Morada:</span>
                        <span class="info-value"><?php echo $order['shipping_address']; ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Cidade:</span>
                        <span class="info-value"><?php echo $order['shipping_city']; ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Código Postal:</span>
                        <span class="info-value"><?php echo $order['shipping_postal_code']; ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">País:</span>
                        <span class="info-value"><?php echo $order['shipping_country']; ?></span>
                    </div>
                    
                    <?php if ($user): ?>
                    <div class="info-row">
                        <span class="info-label">Conta:</span>
                        <span class="info-value">
                            <a href="<?php echo SITE_URL; ?>/admin/utilizadores/<?php echo $user['id']; ?>">
                                <?php echo $user['name']; ?>
                            </a>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="order-items-card">
            <div class="card-header">
                <h2>Produtos</h2>
            </div>
            <div class="card-content">
                <div class="table-content">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Preço</th>
                            <th>Quantidade</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td>
                                <div class="product-info">
                                    <div class="product-image">
                                    <?php if ($item['image']): ?>
                                    <img src="<?php echo htmlspecialchars(vincit_product_image_url($item['image'] ?? '')); ?>" alt="<?php echo htmlspecialchars($item['product_name'] ?? ''); ?>">
                                    <?php else: ?>
                                    <div class="image-placeholder">
                                        <i class="fas fa-image"></i>
                                    </div>
                                    <?php endif; ?>
                                    </div>
                                    <span class="product-info__name"><?php
                                        $pn = trim((string) ($item['product_name'] ?? ''));
                                        echo htmlspecialchars($pn !== '' ? $pn : 'Artigo sem nome', ENT_QUOTES, 'UTF-8');
                                    ?><?php if (!empty($item['size_label'])): ?> <small class="product-info__size">(<?php echo htmlspecialchars($item['size_label']); ?>)</small><?php endif; ?></span>
                                </div>
                            </td>
                            <td><?php echo formatPrice($item['price']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo formatPrice($item['total']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                            <td><?php echo formatPrice($order['subtotal']); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Envio:</strong></td>
                            <td><?php echo formatPrice($order['shipping_cost']); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total:</strong></td>
                            <td><strong><?php echo formatPrice($order['total']); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
                </div>
            </div>
        </div>
        
        <!-- Código de rastreio -->
        <div class="update-status-card">
            <div class="card-header">
                <h2>Rastreio</h2>
            </div>
            <div class="card-content">
                <form action="<?php echo SITE_URL; ?>/admin/encomendas/editar/<?php echo $orderId; ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="update_tracking">
                    <div class="form-group">
                        <label for="tracking_code">Código de rastreio (ex.: CTT / DPD)</label>
                        <input type="text" id="tracking_code" name="tracking_code" value="<?php echo htmlspecialchars($order['tracking_code'] ?? ''); ?>" maxlength="160" placeholder="Opcional — visível no email ao cliente quando mudares o estado para Enviada/Entregue" style="max-width:100%;">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-truck"></i> Guardar rastreio
                    </button>
                </form>
            </div>
        </div>

        <!-- Update Status -->
        <div class="update-status-card">
            <div class="card-header">
                <h2>Atualizar Estado</h2>
            </div>
            <div class="card-content">
                <form action="<?php echo SITE_URL; ?>/admin/encomendas/editar/<?php echo $orderId; ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="update_status">
                    
                    <div class="form-group">
                        <label for="status">Estado da Encomenda</label>
                        <select id="status" name="status" required>
                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pendente</option>
                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Em processamento</option>
                            <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Enviado</option>
                            <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Entregue</option>
                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_status">Estado de Pagamento</label>
                        <select id="payment_status" name="payment_status" required>
                            <option value="pending" <?php echo $order['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pendente</option>
                            <option value="paid" <?php echo $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Pago</option>
                            <option value="failed" <?php echo $order['payment_status'] === 'failed' ? 'selected' : ''; ?>>Falhou</option>
                            <option value="refunded" <?php echo $order['payment_status'] === 'refunded' ? 'selected' : ''; ?>>Reembolsado</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Atualizar Estado
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Notes -->
        <?php if ($order['notes']): ?>
        <div class="notes-card">
            <div class="card-header">
                <h2>Notas</h2>
            </div>
            <div class="card-content">
                <p><?php echo nl2br(htmlspecialchars($order['notes'], ENT_QUOTES, 'UTF-8')); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Incluir footer do admin
include_once __DIR__ . '/../includes/admin_footer.php';
?>