<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Página do Carrinho
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../includes/payment_trust.php';

$pageTitle = 'Carrinho';

$cartItems = getCart();
$cartTotal = getCartTotal();
$shippingCost = vincit_shipping_cost();
$freeShippingThreshold = vincit_free_shipping_threshold();

$subtotal = $cartTotal;
$total = $subtotal + $shippingCost;

if ($subtotal >= $freeShippingThreshold) {
    $shippingCost = 0;
    $total = $subtotal;
}

$itemCount = count($cartItems);
$qtyTotal = 0;
foreach ($cartItems as $ci) {
    $qtyTotal += (int) ($ci['quantity'] ?? 0);
}

include_once __DIR__ . '/../includes/header.php';
?>

<section class="breadcrumb">
    <div class="container">
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb-list">
                <li><a href="<?php echo SITE_URL; ?>">Início</a></li>
                <li>Carrinho</li>
            </ol>
        </nav>
    </div>
</section>

<section class="cart-section<?php echo $itemCount === 0 ? ' cart-section--empty' : ''; ?>">
    <div class="container cart-section__inner">

        <?php if ($itemCount === 0): ?>

        <div class="empty-cart-shell">
            <div class="empty-cart-visual" aria-hidden="true">
                <div class="empty-cart-visual-ring"></div>
                <div class="empty-cart-visual-float empty-cart-visual-float--1"></div>
                <div class="empty-cart-visual-float empty-cart-visual-float--2"></div>
                <div class="empty-cart-visual-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
            </div>
            <div class="empty-cart-copy">
                <p class="empty-cart-kicker">Carrinho</p>
                <h1 class="empty-cart-title">Ainda não tens artigos aqui</h1>
                <p class="empty-cart-lead">Explora a loja, adiciona peças ao carrinho e volta quando quiseres finalizar a encomenda.</p>
                <ul class="empty-cart-perks">
                    <li><i class="fas fa-truck"></i> Portes grátis a partir de <?php echo formatPrice($freeShippingThreshold); ?></li>
                    <li><i class="fas fa-shield-alt"></i> Pagamento seguro (Stripe)</li>
                    <li><i class="fas fa-tshirt"></i> Streetwear selecionado para ti</li>
                </ul>
                <div class="empty-cart-actions">
                    <a href="<?php echo SITE_URL; ?>/loja" class="empty-cart-btn empty-cart-btn--primary">
                        <i class="fas fa-th-large"></i> Ver loja
                    </a>
                    <a href="<?php echo SITE_URL; ?>" class="empty-cart-btn empty-cart-btn--ghost">Página inicial</a>
                </div>
            </div>
        </div>

        <?php else: ?>

        <?php if (!isLoggedIn()): ?>
        <p class="cart-login-hint">
            Para finalizar a compra é necessário <a href="<?php echo SITE_URL; ?>/login?redirect=<?php echo urlencode(SITE_URL . '/checkout'); ?>">iniciar sessão</a>
            ou <a href="<?php echo SITE_URL; ?>/registo?redirect=<?php echo urlencode(SITE_URL . '/checkout'); ?>">criar conta</a>.
        </p>
        <?php endif; ?>

        <div class="cart-page-header">
            <h1 class="cart-page-title"><i class="fas fa-shopping-bag" aria-hidden="true"></i> O teu carrinho</h1>
            <span class="cart-page-badge"><?php echo (int) $qtyTotal; ?> artigo<?php echo $qtyTotal !== 1 ? 's' : ''; ?></span>
        </div>

        <div class="cart-layout">
            <div class="cart-items" id="cart-items-list">
                <?php foreach ($cartItems as $item):
                    $unitPrice = $item['sale_price'] !== null && $item['sale_price'] !== '' ? (float) $item['sale_price'] : (float) $item['price'];
                    $lineTotal = $unitPrice * (int) $item['quantity'];
                    $slug = !empty($item['slug']) ? $item['slug'] : generateSlug($item['name']);
                    $variantStock = isset($item['variant_stock']) ? (int) $item['variant_stock'] : (int) ($item['stock'] ?? 0);
                    $maxQty = min(99, max(1, $variantStock));
                ?>
                <article class="cart-item-card" data-cart-id="<?php echo (int) $item['id']; ?>" data-max-qty="<?php echo (int) $maxQty; ?>">
                    <a href="<?php echo SITE_URL; ?>/produto/<?php echo htmlspecialchars($slug); ?>" class="cart-item-card__image">
                        <?php if (!empty($item['image'])): ?>
                        <img src="<?php echo htmlspecialchars(vincit_product_image_url($item['image'] ?? '')); ?>" alt="" width="180" height="180" loading="lazy">
                        <?php else: ?>
                        <span class="cart-item-card__placeholder"><i class="fas fa-image"></i></span>
                        <?php endif; ?>
                    </a>
                    <div class="cart-item-card__body">
                        <h2 class="cart-item-card__title">
                            <a href="<?php echo SITE_URL; ?>/produto/<?php echo htmlspecialchars($slug); ?>"><?php echo htmlspecialchars($item['name']); ?></a>
                        </h2>
                        <?php if (!empty($item['brand'])): ?>
                        <p class="cart-item-card__brand"><?php echo htmlspecialchars($item['brand']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($item['cart_size_label'])): ?>
                        <p class="cart-item-card__size">Tamanho: <strong><?php echo htmlspecialchars($item['cart_size_label']); ?></strong></p>
                        <?php endif; ?>
                        <p class="cart-item-card__unit">
                            <?php if ($item['sale_price']): ?>
                            <span class="cart-item-card__unit-was"><?php echo formatPrice($item['price']); ?></span>
                            <span class="cart-item-card__unit-now"><?php echo formatPrice($item['sale_price']); ?> <span class="cart-item-card__each">cada</span></span>
                            <?php else: ?>
                            <span class="cart-item-card__unit-now"><?php echo formatPrice($item['price']); ?> <span class="cart-item-card__each">cada</span></span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="cart-item-card__qty">
                        <span class="cart-item-card__label">Quantidade</span>
                        <div class="quantity-controls">
                            <button type="button" class="quantity-btn minus" data-cart-id="<?php echo (int) $item['id']; ?>" aria-label="Menos um">−</button>
                            <input type="number" class="quantity-input cart-quantity" data-cart-id="<?php echo (int) $item['id']; ?>" value="<?php echo (int) $item['quantity']; ?>" min="1" max="<?php echo (int) $maxQty; ?>" inputmode="numeric" aria-label="Quantidade">
                            <button type="button" class="quantity-btn plus" data-cart-id="<?php echo (int) $item['id']; ?>" aria-label="Mais um">+</button>
                        </div>
                    </div>
                    <div class="cart-item-card__line-total">
                        <span class="cart-item-card__label">Total</span>
                        <span class="cart-item-card__total-price" data-line-total><?php echo formatPrice($lineTotal); ?></span>
                    </div>
                    <div class="cart-item-card__remove">
                        <button type="button" class="remove-from-cart" data-cart-id="<?php echo (int) $item['id']; ?>" aria-label="Remover artigo">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

            <aside class="cart-summary-card">
                <h2 class="cart-summary-card__heading"><i class="fas fa-receipt" aria-hidden="true"></i> Resumo</h2>
                <div class="cart-summary-card__row">
                    <span>Subtotal (<?php echo (int) $qtyTotal; ?>)</span>
                    <span class="cart-subtotal"><?php echo formatPrice($subtotal); ?></span>
                </div>
                <div class="cart-summary-card__row">
                    <span>Envio</span>
                    <span class="shipping-cost">
                        <?php if ($shippingCost > 0): ?>
                        <?php echo formatPrice($shippingCost); ?>
                        <?php else: ?>
                        <span class="free-shipping">Grátis</span>
                        <?php endif; ?>
                    </span>
                </div>
                <?php if ($subtotal < $freeShippingThreshold && $subtotal > 0): ?>
                <div class="cart-summary-card__hint">
                    <i class="fas fa-truck" aria-hidden="true"></i>
                    <span>Faltam <?php echo formatPrice($freeShippingThreshold - $subtotal); ?> para envio grátis.</span>
                </div>
                <?php endif; ?>
                <div class="cart-summary-card__row cart-summary-card__row--grand">
                    <span>Total</span>
                    <span class="cart-total"><?php echo formatPrice($total); ?></span>
                </div>
                <div class="cart-summary-card__actions">
                    <?php if (isLoggedIn()): ?>
                    <a href="<?php echo SITE_URL; ?>/checkout" class="btn btn-primary btn-lg btn-block">Finalizar compra</a>
                    <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/login?redirect=<?php echo urlencode(SITE_URL . '/checkout'); ?>" class="btn btn-primary btn-lg btn-block">Iniciar sessão para finalizar</a>
                    <?php endif; ?>
                    <a href="<?php echo SITE_URL; ?>/loja" class="btn btn-outline btn-block">Continuar a comprar</a>
                </div>
                <div class="cart-summary-card__trust">
                    <?php payment_trust_render('product'); ?>
                </div>
            </aside>
        </div>

        <?php endif; ?>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
