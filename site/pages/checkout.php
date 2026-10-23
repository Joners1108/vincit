<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Página de Checkout
 */

// Incluir ficheiros de configuração e base de dados
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../includes/product_sizes_lib.php';
require_once __DIR__ . '/../includes/stripe_checkout.php';

$stripeCheckoutReady = vincit_stripe_configured();
$allowSimulatedCheckout = env_bool('ALLOW_SIMULATED_CHECKOUT', false);
$checkoutPaymentBlocked = !$stripeCheckoutReady && !$allowSimulatedCheckout;

// Definir título da página
$pageTitle = 'Checkout';

// Verificar se carrinho não está vazio
$cartItems = getCart();
if (count($cartItems) === 0) {
    redirect(SITE_URL . '/carrinho');
}

if (!isLoggedIn()) {
    $_SESSION['after_login_redirect'] = SITE_URL . '/checkout';
    $_SESSION['notification'] = [
        'type' => 'info',
        'message' => 'Inicia sessão ou regista-te para finalizar a encomenda.',
    ];
    redirect(SITE_URL . '/login?redirect=' . urlencode(SITE_URL . '/checkout'));
}

$currentUser = getCurrentUser();
if (!$currentUser) {
    redirect(SITE_URL . '/login?redirect=' . urlencode(SITE_URL . '/checkout'));
}

// Calcular totais
$cartTotal = getCartTotal();
$shippingCost = vincit_shipping_cost();
$freeShippingThreshold = vincit_free_shipping_threshold();

// Verificar se tem envio grátis
if ($cartTotal >= $freeShippingThreshold) {
    $shippingCost = 0;
}

$subtotal = $cartTotal;
$total = $subtotal + $shippingCost;

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Token de segurança inválido'
        ];
        redirect(SITE_URL . '/checkout');
    }
    
    // Obter dados do formulário
    $shippingName = clean($_POST['shipping_name'] ?? '');
    $shippingEmail = clean($_POST['shipping_email'] ?? '');
    $shippingPhone = clean($_POST['shipping_phone'] ?? '');
    $shippingAddress = clean($_POST['shipping_address'] ?? '');
    $shippingCity = clean($_POST['shipping_city'] ?? '');
    $shippingPostalCode = clean($_POST['shipping_postal_code'] ?? '');
    $shippingCountry = clean($_POST['shipping_country'] ?? 'Portugal');
    $paymentMethod = 'stripe';
    $notes = clean($_POST['notes'] ?? '');
    
    // Validar campos obrigatórios
    $errors = [];
    
    if (empty($shippingName)) {
        $errors[] = 'Nome é obrigatório';
    }
    
    if (empty($shippingEmail) || !filter_var($shippingEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    }
    
    if (empty($shippingAddress)) {
        $errors[] = 'Morada é obrigatória';
    }
    
    if (empty($shippingCity)) {
        $errors[] = 'Cidade é obrigatória';
    }
    
    if (empty($shippingPostalCode)) {
        $errors[] = 'Código postal é obrigatório';
    }
    
    // Se houver erros, redirecionar
    if (count($errors) > 0) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => implode('<br>', $errors)
        ];
        redirect(SITE_URL . '/checkout');
    }

    $cartItems = getCart();
    if (count($cartItems) === 0) {
        redirect(SITE_URL . '/carrinho');
    }

    foreach ($cartItems as $item) {
        $avail = (int) ($item['variant_stock'] ?? $item['stock'] ?? 0);
        if ($avail < (int) $item['quantity']) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Stock insuficiente para: ' . ($item['name'] ?? 'produto')
                    . (!empty($item['cart_size_label']) ? ' (tamanho ' . $item['cart_size_label'] . ')' : ''),
            ];
            redirect(SITE_URL . '/carrinho');
        }
    }

    $recalcTotal = getCartTotal();
    $shippingCostPost = vincit_shipping_cost();
    if ($recalcTotal >= vincit_free_shipping_threshold()) {
        $shippingCostPost = 0;
    }
    $subtotalPost = $recalcTotal;
    $totalPost = $subtotalPost + $shippingCostPost;

    require_once __DIR__ . '/../includes/stripe_checkout.php';

    // Sem Stripe configurado: só permite "pagamento simulado" se ALLOW_SIMULATED_CHECKOUT=true no .env (dev).
    if (!vincit_stripe_configured()) {
        if (!env_bool('ALLOW_SIMULATED_CHECKOUT', false)) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Pagamento online indisponível. Confirma STRIPE_SECRET_KEY no ficheiro .env (chave sk_test_… ou sk_live_…) e grava o ficheiro.',
            ];
            redirect(SITE_URL . '/checkout');
        }
    }

    try {
        dbBeginTransaction();

        $orderNumber = generateOrderNumber();

        $orderId = dbInsert('orders', [
            'user_id' => $currentUser['id'],
            'cart_session_id' => null,
            'order_number' => $orderNumber,
            'total' => $totalPost,
            'subtotal' => $subtotalPost,
            'shipping_cost' => $shippingCostPost,
            'tax' => 0,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => $paymentMethod,
            'shipping_name' => $shippingName,
            'shipping_email' => $shippingEmail,
            'shipping_phone' => $shippingPhone,
            'shipping_address' => $shippingAddress,
            'shipping_city' => $shippingCity,
            'shipping_postal_code' => $shippingPostalCode,
            'shipping_country' => $shippingCountry,
            'notes' => $notes,
        ]);

        if (!$orderId) {
            throw new Exception('Erro ao criar encomenda');
        }

        foreach ($cartItems as $item) {
            $itemPrice = $item['sale_price'] ?? $item['price'];
            $itemTotal = $itemPrice * $item['quantity'];

            dbInsert('order_items', [
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'product_size_id' => (int) $item['product_size_id'],
                'product_name' => $item['name'],
                'size_label' => $item['cart_size_label'] ?? null,
                'quantity' => $item['quantity'],
                'price' => $itemPrice,
                'total' => $itemTotal,
            ]);
        }

        require_once __DIR__ . '/../includes/admin_notifications_lib.php';
        vincit_admin_notify_insert(
            'Nova encomenda',
            'Pedido ' . $orderNumber . ' — aguarda pagamento.',
            rtrim(SITE_URL, '/') . '/admin/encomendas/editar/' . $orderId
        );

        if (vincit_stripe_configured()) {
            $lineItems = [];
            foreach ($cartItems as $item) {
                $itemPrice = $item['sale_price'] ?? $item['price'];
                $descParts = array_filter([
                    $item['brand'] ?? '',
                    !empty($item['cart_size_label']) ? 'Tamanho: ' . $item['cart_size_label'] : '',
                    isset($item['color']) ? 'Cor: ' . $item['color'] : '',
                ]);
                $description = trim(implode(' · ', $descParts));
                if ($description === '') {
                    $description = 'Produto';
                }
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $item['name'],
                            'description' => $description,
                        ],
                        'unit_amount' => (int) round((float) $itemPrice * 100),
                    ],
                    'quantity' => (int) $item['quantity'],
                ];
            }
            if ($shippingCostPost > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Portes de envio',
                            'description' => $shippingCountry,
                        ],
                        'unit_amount' => (int) round((float) $shippingCostPost * 100),
                    ],
                    'quantity' => 1,
                ];
            }

            $metaUser = (string) $currentUser['id'];
            $metaCart = '';

            $stripeResult = vincit_create_stripe_checkout_session(
                (int) $orderId,
                $shippingEmail,
                $lineItems,
                $metaUser,
                $metaCart
            );

            if (empty($stripeResult['success'])) {
                throw new Exception($stripeResult['error'] ?? 'Erro ao iniciar pagamento Stripe');
            }

            dbUpdate('orders', [
                'stripe_checkout_session_id' => $stripeResult['session_id'],
            ], 'id = ?', [$orderId]);

            dbCommit();

            header('Location: ' . $stripeResult['url']);
            exit;
        }

        $stockErr = vincit_apply_cart_stock_decrements($cartItems);
        if ($stockErr !== null) {
            throw new Exception($stockErr);
        }

        dbUpdate('orders', [
            'payment_status' => 'paid',
            'status' => 'processing',
            'stripe_payment_id' => 'sim_' . uniqid(),
            'payment_method' => 'simulated',
        ], 'id = ?', [$orderId]);

        clearCart();

        dbCommit();

        require_once __DIR__ . '/../includes/mailer.php';
        $orderForMail = getOrderById($orderId);
        $itemsForMail = getOrderItems($orderId);
        if ($orderForMail) {
            vincit_send_order_confirmation_customer_email($orderForMail, $itemsForMail);
            vincit_send_new_order_paid_admin_email($orderForMail, $itemsForMail);
        }
        vincit_admin_notify_insert(
            'Encomenda paga',
            'Pagamento confirmado para ' . $orderNumber . '.',
            rtrim(SITE_URL, '/') . '/admin/encomendas/editar/' . $orderId
        );

        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Encomenda realizada com sucesso! Número: ' . $orderNumber,
        ];

        redirect(SITE_URL . '/conta');
    } catch (Exception $e) {
        dbRollback();

        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Erro ao processar encomenda: ' . $e->getMessage(),
        ];

        redirect(SITE_URL . '/checkout');
    }
}
$additionalJS = [];
if (ADDRESS_AUTOCOMPLETE_ENABLED) {
    $additionalJS[] = 'checkout-address.js';
}

// Incluir header
require_once __DIR__ . '/../includes/payment_trust.php';
include_once __DIR__ . '/../includes/header.php';
?>
<?php if (ADDRESS_AUTOCOMPLETE_ENABLED): ?>
<script>window.VINCIT_ADDRESS_API = <?php echo json_encode(SITE_URL . '/api/address-suggest', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;</script>
<?php endif; ?>

<!-- Breadcrumb -->
<section class="breadcrumb">
    <div class="container">
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb-list">
                <li><a href="<?php echo SITE_URL; ?>">Início</a></li>
                <li><a href="<?php echo SITE_URL; ?>/carrinho">Carrinho</a></li>
                <li>Checkout</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Checkout Section -->
<section class="checkout-section">
    <div class="container">
        <h1 class="page-title">Checkout</h1>
        
        <form action="<?php echo SITE_URL; ?>/checkout" method="POST" class="checkout-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="checkout-layout">
                <!-- Checkout Form -->
                <div class="checkout-form-container">
                    <!-- Shipping Information -->
                    <div class="checkout-step">
                        <h2><i class="fas fa-truck"></i> Informações de Envio</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="shipping_name">Nome Completo *</label>
                                <input type="text" id="shipping_name" name="shipping_name" value="<?php echo $currentUser ? $currentUser['name'] : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="shipping_email">Email *</label>
                                <input type="email" id="shipping_email" name="shipping_email" value="<?php echo $currentUser ? $currentUser['email'] : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="shipping_phone">Telefone</label>
                                <input type="tel" id="shipping_phone" name="shipping_phone" value="<?php echo $currentUser ? $currentUser['phone'] : ''; ?>">
                            </div>
                        </div>
                        
                        <?php if (ADDRESS_AUTOCOMPLETE_ENABLED): ?>
                        <div class="form-row">
                            <div class="form-group address-lookup-wrap">
                                <label for="address_lookup">Pesquisar morada</label>
                                <input type="search" id="address_lookup" name="address_lookup" autocomplete="off" placeholder="Escreve rua, localidade ou código postal (Portugal)…" aria-autocomplete="list" aria-controls="address_suggestions">
                                <ul id="address_suggestions" class="address-suggestions" hidden></ul>
                                <p class="address-lookup-hint">Escolhe uma sugestão para preencher morada, cidade e código postal. Podes ajustar o texto depois.</p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="shipping_address">Morada *</label>
                                <textarea id="shipping_address" name="shipping_address" rows="3" required><?php echo $currentUser ? $currentUser['address'] : ''; ?></textarea>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="shipping_city">Cidade *</label>
                                <input type="text" id="shipping_city" name="shipping_city" value="<?php echo $currentUser ? $currentUser['city'] : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="shipping_postal_code">Código Postal *</label>
                                <input type="text" id="shipping_postal_code" name="shipping_postal_code" value="<?php echo $currentUser ? $currentUser['postal_code'] : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="shipping_country">País *</label>
                                <select id="shipping_country" name="shipping_country" required>
                                    <option value="Portugal" <?php echo (!$currentUser || $currentUser['country'] === 'Portugal') ? 'selected' : ''; ?>>Portugal</option>
                                    <option value="Espanha" <?php echo ($currentUser && $currentUser['country'] === 'Espanha') ? 'selected' : ''; ?>>Espanha</option>
                                    <option value="França" <?php echo ($currentUser && $currentUser['country'] === 'França') ? 'selected' : ''; ?>>França</option>
                                    <option value="Alemanha" <?php echo ($currentUser && $currentUser['country'] === 'Alemanha') ? 'selected' : ''; ?>>Alemanha</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="notes">Notas da Encomenda</label>
                                <textarea id="notes" name="notes" rows="3" placeholder="Instruções especiais para entrega..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pagamento: Stripe Checkout hospedado -->
                    <div class="checkout-step">
                        <h2><i class="fas fa-credit-card"></i> Pagamento</h2>
                        <input type="hidden" name="payment_method" value="stripe">
                        <?php if ($checkoutPaymentBlocked): ?>
                        <div class="form-group" style="padding:1rem;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;color:#991b1b;font-size:0.95rem;">
                            <strong>Pagamento indisponível.</strong> A loja ainda não tem <code>STRIPE_SECRET_KEY</code> válida no <code>.env</code>.
                            Para testes locais sem Stripe, define <code>ALLOW_SIMULATED_CHECKOUT=true</code> no <code>.env</code> (só desenvolvimento).
                        </div>
                        <?php elseif ($stripeCheckoutReady): ?>
                        <p style="font-size:0.95rem;color:#475569;margin:0 0 0.75rem;">Ao finalizar, serás redirecionado para o <strong>Stripe</strong> para pagar com cartão de forma segura.</p>
                        <?php else: ?>
                        <p style="font-size:0.95rem;color:#b45309;margin:0 0 0.75rem;"><strong>Modo de teste:</strong> sem Stripe configurado — a encomenda será marcada como paga automaticamente (<code>ALLOW_SIMULATED_CHECKOUT=true</code>).</p>
                        <?php endif; ?>
                        <?php payment_trust_render('checkout'); ?>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="checkout-summary">
                    <h2>Resumo do Pedido</h2>
                    
                    <div class="order-items">
                        <?php foreach ($cartItems as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <?php if ($item['image']): ?>
                                <img src="<?php echo htmlspecialchars(vincit_product_image_url($item['image'] ?? '')); ?>" alt="<?php echo $item['name']; ?>">
                                <?php else: ?>
                                <div class="item-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="item-details">
                                <h4><?php echo $item['name']; ?></h4>
                                <span class="item-quantity">Qtd: <?php echo $item['quantity']; ?></span>
                            </div>
                            <div class="item-price">
                                <?php 
                                $itemPrice = $item['sale_price'] ?? $item['price'];
                                $itemTotal = $itemPrice * $item['quantity'];
                                echo formatPrice($itemTotal);
                                ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="summary-totals">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Envio:</span>
                            <span>
                                <?php if ($shippingCost > 0): ?>
                                <?php echo formatPrice($shippingCost); ?>
                                <?php else: ?>
                                <span class="free-shipping">Grátis</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="summary-total">
                            <span>Total:</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg btn-block"<?php echo $checkoutPaymentBlocked ? ' disabled aria-disabled="true"' : ''; ?>>
                        <i class="fas fa-lock"></i> <?php echo $stripeCheckoutReady ? 'Continuar para pagamento' : 'Finalizar compra'; ?>
                    </button>
                    
                    <div class="checkout-security">
                        <i class="fas fa-shield-alt"></i>
                        <span>Pagamento 100% seguro</span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php
// Incluir footer
include_once __DIR__ . '/../includes/footer.php';
?>