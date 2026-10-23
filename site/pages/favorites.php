<?php
/**
 * Favoritos (login obrigatório)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../includes/favorites_lib.php';

if (!isLoggedIn()) {
    $_SESSION['after_login_redirect'] = SITE_URL . '/favoritos';
    $_SESSION['notification'] = ['type' => 'info', 'message' => 'Inicia sessão para ver os teus favoritos.'];
    redirect(SITE_URL . '/login?redirect=' . urlencode(SITE_URL . '/favoritos'));
}

$pageTitle = 'Favoritos';

$uid = (int) $_SESSION['user_id'];
$products = dbFetchAll(
    "SELECT p.*, c.name as category_name
     FROM favorites f
     INNER JOIN products p ON p.id = f.product_id
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE f.user_id = ? AND p.status = 'active'
     ORDER BY f.created_at DESC",
    [$uid]
);

include_once __DIR__ . '/../includes/header.php';
?>

<section class="breadcrumb">
    <div class="container">
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb-list">
                <li><a href="<?php echo SITE_URL; ?>">Início</a></li>
                <li>Favoritos</li>
            </ol>
        </nav>
    </div>
</section>

<section class="featured-section">
    <div class="container">
        <h1 class="page-title">Os meus favoritos</h1>
        <?php if (count($products) === 0): ?>
        <p style="text-align:center;color:#6b7280;margin:2rem 0;">Ainda não guardaste produtos. Explora a <a href="<?php echo SITE_URL; ?>/loja">loja</a>.</p>
        <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <a href="<?php echo SITE_URL; ?>/produto/<?php echo htmlspecialchars($product['slug']); ?>">
                        <?php if ($product['image']): ?>
                        <img src="<?php echo htmlspecialchars(vincit_product_image_url($product['image'] ?? '')); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                        <div class="product-placeholder"><i class="fas fa-image"></i></div>
                        <?php endif; ?>
                    </a>
                    <?php if ($product['sale_price']): ?>
                    <span class="sale-badge">Promoção</span>
                    <?php endif; ?>
                    <button type="button" class="add-to-cart-btn" data-product-id="<?php echo (int) $product['id']; ?>"><i class="fas fa-shopping-cart"></i></button>
                </div>
                <div class="product-info">
                    <h3><a href="<?php echo SITE_URL; ?>/produto/<?php echo htmlspecialchars($product['slug']); ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                    <div class="product-price">
                        <?php if ($product['sale_price']): ?>
                        <span class="original-price"><?php echo formatPrice($product['price']); ?></span>
                        <span class="sale-price"><?php echo formatPrice($product['sale_price']); ?></span>
                        <?php else: ?>
                        <span class="price"><?php echo formatPrice($product['price']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
