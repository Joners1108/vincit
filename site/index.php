<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Página Inicial
 */

// Incluir ficheiros de configuração e base de dados
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

// Obter produtos em destaque
$featuredProducts = getProducts(['featured' => true, 'limit' => 8]);

// Obter categorias
$categories = getCategories(true);

// Obter produtos mais recentes
$latestProducts = getProducts(['limit' => 8]);

// Incluir header
include_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1><?php echo htmlspecialchars(SITE_NAME); ?></h1>
            <p>Nova coleção, drops e peças de streetwear — feito para quem vive a cidade.</p>
            <a href="<?php echo SITE_URL; ?>/loja" class="btn btn-primary">Ver loja</a>
        </div>
    </div>
</section>

<!-- Categorias Section -->
<section class="categories-section">
    <div class="container">
        <h2 class="section-title">Categorias</h2>
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
            <a href="<?php echo SITE_URL; ?>/loja/categoria/<?php echo $category['slug']; ?>" class="category-card">
                <div class="category-image">
                    <?php if ($category['image']): ?>
                    <img src="<?php echo htmlspecialchars(vincit_category_image_url($category['image'] ?? '')); ?>" alt="<?php echo $category['name']; ?>">
                    <?php else: ?>
                    <div class="category-placeholder">
                        <i class="fas fa-tshirt"></i>
                    </div>
                    <?php endif; ?>
                </div>
                <h3><?php echo $category['name']; ?></h3>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Produtos em Destaque -->
<section class="featured-section">
    <div class="container">
        <h2 class="section-title">Produtos em Destaque</h2>
        <div class="products-grid">
            <?php foreach ($featuredProducts as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <a href="<?php echo SITE_URL; ?>/produto/<?php echo $product['slug']; ?>">
                        <?php if ($product['image']): ?>
                        <img src="<?php echo htmlspecialchars(vincit_product_image_url($product['image'] ?? '')); ?>" alt="<?php echo $product['name']; ?>">
                        <?php else: ?>
                        <div class="product-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                        <?php endif; ?>
                    </a>
                    <?php if ($product['sale_price']): ?>
                    <span class="sale-badge">Promoção</span>
                    <?php endif; ?>
                    <button class="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                        <i class="fas fa-shopping-cart"></i>
                    </button>
                </div>
                <div class="product-info">
                    <h3><a href="<?php echo SITE_URL; ?>/produto/<?php echo $product['slug']; ?>"><?php echo $product['name']; ?></a></h3>
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
        <div class="section-footer">
            <a href="<?php echo SITE_URL; ?>/loja" class="btn btn-secondary">Ver Todos os Produtos</a>
        </div>
    </div>
</section>

<!-- Novidades Section -->
<section class="latest-section">
    <div class="container">
        <h2 class="section-title">Novidades</h2>
        <div class="products-grid">
            <?php foreach ($latestProducts as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <a href="<?php echo SITE_URL; ?>/produto/<?php echo $product['slug']; ?>">
                        <?php if ($product['image']): ?>
                        <img src="<?php echo htmlspecialchars(vincit_product_image_url($product['image'] ?? '')); ?>" alt="<?php echo $product['name']; ?>">
                        <?php else: ?>
                        <div class="product-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                        <?php endif; ?>
                    </a>
                    <?php if ($product['sale_price']): ?>
                    <span class="sale-badge">Promoção</span>
                    <?php endif; ?>
                    <button class="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                        <i class="fas fa-shopping-cart"></i>
                    </button>
                </div>
                <div class="product-info">
                    <h3><a href="<?php echo SITE_URL; ?>/produto/<?php echo $product['slug']; ?>"><?php echo $product['name']; ?></a></h3>
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
    </div>
</section>

<?php
// Incluir footer
include_once __DIR__ . '/includes/footer.php';
?>
