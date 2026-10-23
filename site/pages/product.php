<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Página de Produto
 */

// Incluir ficheiros de configuração e base de dados
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../includes/product_photos.php';
require_once __DIR__ . '/../includes/favorites_lib.php';
require_once __DIR__ . '/../includes/product_sizes_lib.php';

// Obter slug do produto
$slug = $_GET['slug'] ?? null;

if (!$slug) {
    redirect(SITE_URL . '/loja');
}

// Obter produto
$product = getProductBySlug($slug);

if (!$product) {
    redirect(SITE_URL . '/loja');
}

$isFavorite = isLoggedIn() && vincit_is_favorite((int) $product['id']);

$productSizes = vincit_get_product_sizes((int) $product['id']);
$preselectSizeId = '';
$preselectMaxQty = 0;
if (count($productSizes) === 1) {
    $only = $productSizes[0];
    $preselectSizeId = (string) (int) $only['id'];
    $preselectMaxQty = max(0, (int) $only['stock']);
}

$productPhotos = getProductPhotosOrdered($product);

// Definir título da página
$pageTitle = $product['name'];

// Obter produtos relacionados
$relatedProducts = getProducts([
    'category_id' => $product['category_id'],
    'limit' => 4
]);

// Remover produto atual dos relacionados
$relatedProducts = array_filter($relatedProducts, function($p) use ($product) {
    return $p['id'] !== $product['id'];
});

// Obter avaliações do produto
$reviews = dbFetchAll("SELECT r.*, u.name as user_name 
                      FROM reviews r 
                      JOIN users u ON r.user_id = u.id 
                      WHERE r.product_id = ? AND r.status = 'approved' 
                      ORDER BY r.created_at DESC", [$product['id']]);

// Calcular média de avaliações
$avgRating = 0;
if (count($reviews) > 0) {
    $totalRating = array_sum(array_column($reviews, 'rating'));
    $avgRating = $totalRating / count($reviews);
}

$userHasReview = false;
if (isLoggedIn()) {
    $userHasReview = dbFetch(
        'SELECT id FROM reviews WHERE product_id = ? AND user_id = ? LIMIT 1',
        [(int) $product['id'], (int) $_SESSION['user_id']]
    ) !== null;
}

// Incluir header
require_once __DIR__ . '/../includes/payment_trust.php';
include_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<section class="breadcrumb">
    <div class="container">
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb-list">
                <li><a href="<?php echo SITE_URL; ?>">Início</a></li>
                <li><a href="<?php echo SITE_URL; ?>/loja">Loja</a></li>
                <?php if ($product['category_name']): ?>
                <li><a href="<?php echo SITE_URL; ?>/loja/categoria/<?php echo generateSlug($product['category_name']); ?>"><?php echo $product['category_name']; ?></a></li>
                <?php endif; ?>
                <li><?php echo $product['name']; ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Product Section -->
<section class="product-section">
    <div class="container">
        <div class="product-layout">
            <!-- Product Gallery -->
            <div class="product-gallery" data-product-gallery>
                <div class="main-image">
                    <?php if (!empty($productPhotos)): ?>
                    <img id="product-main-img" src="<?php echo htmlspecialchars(vincit_product_image_url($productPhotos[0])); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php else: ?>
                    <div class="product-placeholder">
                        <i class="fas fa-image"></i>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if (count($productPhotos) > 1): ?>
                <div class="thumbnail-gallery" role="tablist" aria-label="Fotografias do produto">
                    <?php foreach ($productPhotos as $idx => $photoFile): ?>
                    <button type="button" class="thumbnail<?php echo $idx === 0 ? ' active' : ''; ?>" data-image="<?php echo htmlspecialchars(vincit_product_image_url($photoFile)); ?>" aria-label="Ver fotografia <?php echo (int) $idx + 1; ?>">
                        <img src="<?php echo htmlspecialchars(vincit_product_image_url($photoFile)); ?>" alt="">
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Product Info -->
            <div class="product-info">
                <h1 class="product-title"><?php echo $product['name']; ?></h1>
                
                <!-- Rating -->
                <?php if (count($reviews) > 0): ?>
                <div class="product-rating">
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star <?php echo $i <= $avgRating ? 'filled' : ''; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="rating-count">(<?php echo count($reviews); ?> avaliações)</span>
                </div>
                <?php endif; ?>
                
                <!-- Price -->
                <div class="product-price">
                    <?php if ($product['sale_price']): ?>
                    <span class="original-price"><?php echo formatPrice($product['price']); ?></span>
                    <span class="sale-price"><?php echo formatPrice($product['sale_price']); ?></span>
                    <span class="discount-badge">
                        <?php 
                        $discount = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
                        echo '-' . $discount . '%';
                        ?>
                    </span>
                    <?php else: ?>
                    <span class="price"><?php echo formatPrice($product['price']); ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Short Description -->
                <?php if ($product['short_description']): ?>
                <p class="product-short-description"><?php echo $product['short_description']; ?></p>
                <?php endif; ?>
                
                <!-- Product Meta -->
                <div class="product-meta">
                    <?php if ($product['brand']): ?>
                    <div class="meta-item">
                        <span class="meta-label">Marca:</span>
                        <span class="meta-value"><?php echo $product['brand']; ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($product['material']): ?>
                    <div class="meta-item">
                        <span class="meta-label">Material:</span>
                        <span class="meta-value"><?php echo $product['material']; ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="meta-item">
                        <span class="meta-label">Disponibilidade:</span>
                        <span class="meta-value <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                            <?php echo $product['stock'] > 0 ? 'Em stock (' . $product['stock'] . ' un.)' : 'Esgotado'; ?>
                        </span>
                    </div>
                </div>
                
                <!-- Tamanhos (stock por tamanho) -->
                <?php if (count($productSizes) > 0): ?>
                <div class="product-options">
                    <h4>Tamanho: <span class="product-size-hint" id="product-size-hint"><?php echo count($productSizes) > 1 ? 'Escolhe uma opção' : ''; ?></span></h4>
                    <div class="size-options" role="group" aria-label="Tamanho">
                        <?php foreach ($productSizes as $sz) :
                            $sid = (int) $sz['id'];
                            $sqty = (int) $sz['stock'];
                            $out = $sqty <= 0;
                            ?>
                        <button type="button" class="size-btn<?php echo $out ? ' is-out-of-stock' : ''; ?><?php echo ($preselectSizeId !== '' && (int) $preselectSizeId === $sid) ? ' active' : ''; ?>"
                            data-product-size-id="<?php echo $sid; ?>"
                            data-stock="<?php echo $sqty; ?>"
                            <?php echo $out ? 'disabled' : ''; ?>>
                            <?php echo htmlspecialchars($sz['size_label']); ?>
                            <?php if (!$out && $sqty <= 5) : ?>
                            <span class="size-stock-pill"><?php echo $sqty; ?> disp.</span>
                            <?php elseif ($out) : ?>
                            <span class="size-stock-pill size-stock-pill--out">Esgotado</span>
                            <?php endif; ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="product-selected-size-id" value="<?php echo htmlspecialchars($preselectSizeId); ?>">
                </div>
                <?php else: ?>
                <p class="product-no-sizes-msg">Este produto ainda não tem tamanhos configurados. Volta em breve.</p>
                <?php endif; ?>
                
                <!-- Color Selection -->
                <?php if ($product['color']): ?>
                <div class="product-options">
                    <h4>Cor:</h4>
                    <div class="color-options">
                        <?php 
                        $colors = explode(',', $product['color']);
                        foreach ($colors as $color): 
                        ?>
                        <button type="button" class="color-btn" data-color="<?php echo trim($color); ?>" style="background-color: <?php echo trim($color); ?>">
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="color" value="">
                </div>
                <?php endif; ?>
                
                <!-- Add to Cart -->
                <div class="product-actions" data-product-id="<?php echo (int) $product['id']; ?>">
                    <div class="quantity-selector">
                        <button type="button" class="quantity-btn minus">-</button>
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $preselectMaxQty > 0 ? min(99, $preselectMaxQty) : (count($productSizes) > 1 ? 1 : max(1, (int) $product['stock'])); ?>" class="quantity-input">
                        <button type="button" class="quantity-btn plus">+</button>
                    </div>
                    <button type="button" class="btn btn-primary add-to-cart-btn" data-product-id="<?php echo (int) $product['id']; ?>" <?php echo ($product['stock'] <= 0 || count($productSizes) === 0) ? 'disabled' : ''; ?>>
                        <i class="fas fa-shopping-cart"></i>
                        <?php echo ($product['stock'] > 0 && count($productSizes) > 0) ? 'Adicionar ao Carrinho' : 'Esgotado'; ?>
                    </button>
                </div>
                
                <!-- Wishlist -->
                <div class="product-wishlist">
                    <?php if (isLoggedIn()): ?>
                    <button type="button" class="wishlist-btn" data-product-id="<?php echo (int) $product['id']; ?>" data-favorite="<?php echo $isFavorite ? '1' : '0'; ?>" aria-pressed="<?php echo $isFavorite ? 'true' : 'false'; ?>">
                        <i class="<?php echo $isFavorite ? 'fas' : 'far'; ?> fa-heart" aria-hidden="true"></i>
                        <span class="wishlist-btn__text"><?php echo $isFavorite ? 'Nos teus favoritos' : 'Adicionar aos favoritos'; ?></span>
                    </button>
                    <?php else: ?>
                    <p class="wishlist-login-hint"><a href="<?php echo SITE_URL; ?>/login?redirect=<?php echo urlencode(SITE_URL . '/produto/' . $product['slug']); ?>">Inicia sessão</a> para guardar favoritos.</p>
                    <?php endif; ?>
                </div>

                <?php payment_trust_render('product'); ?>
                
            </div>
        </div>
        
        <!-- Product Tabs -->
        <div class="product-tabs">
            <div class="tabs-header">
                <button class="tab-btn active" data-tab="description">Descrição</button>
                <button class="tab-btn" data-tab="reviews">Avaliações (<?php echo count($reviews); ?>)</button>
            </div>
            
            <div class="tabs-content">
                <!-- Description Tab -->
                <div class="tab-pane active" id="description">
                    <?php if ($product['description']): ?>
                    <div class="product-description">
                        <?php echo nl2br($product['description']); ?>
                    </div>
                    <?php else: ?>
                    <p>Descrição não disponível.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Reviews Tab -->
                <div class="tab-pane" id="reviews">
                    <?php if (count($reviews) > 0): ?>
                    <div class="reviews-list">
                        <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-author">
                                    <strong><?php echo htmlspecialchars((string) $review['user_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <span class="review-date"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></span>
                                </div>
                                <div class="review-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'filled' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="review-content">
                                <p><?php echo nl2br(htmlspecialchars((string) $review['comment'], ENT_QUOTES, 'UTF-8')); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p>Este produto ainda não tem avaliações.</p>
                    <?php endif; ?>
                    
                    <?php if (isLoggedIn() && !$userHasReview): ?>
                    <div class="review-form">
                        <h3>Deixe a sua avaliação</h3>
                        <form action="<?php echo SITE_URL; ?>/api/reviews.php" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="product_slug" value="<?php echo htmlspecialchars($product['slug']); ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <div class="form-group">
                                <label>Classificação:</label>
                                <div class="rating-input">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <input type="radio" name="rating" value="<?php echo $i; ?>" id="rating-<?php echo $i; ?>" required>
                                    <label for="rating-<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="comment">Comentário:</label>
                                <textarea name="comment" id="comment" rows="4" required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Enviar Avaliação</button>
                        </form>
                    </div>
                    <?php elseif (isLoggedIn() && $userHasReview): ?>
                    <p class="review-already-msg">Já deixaste uma avaliação neste produto.</p>
                    <?php else: ?>
                    <p><a href="<?php echo SITE_URL; ?>/login?redirect=<?php echo urlencode(SITE_URL . '/produto/' . $product['slug'] . '#reviews'); ?>">Inicie sessão</a> para deixar uma avaliação.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if (count($relatedProducts) > 0): ?>
        <div class="related-products">
            <h2 class="section-title">Produtos Relacionados</h2>
            <div class="products-grid">
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                <div class="product-card">
                    <div class="product-image">
                        <a href="<?php echo SITE_URL; ?>/produto/<?php echo $relatedProduct['slug']; ?>">
                            <?php if ($relatedProduct['image']): ?>
                            <img src="<?php echo htmlspecialchars(vincit_product_image_url($relatedProduct['image'] ?? '')); ?>" alt="<?php echo $relatedProduct['name']; ?>">
                            <?php else: ?>
                            <div class="product-placeholder">
                                <i class="fas fa-image"></i>
                            </div>
                            <?php endif; ?>
                        </a>
                        <?php if ($relatedProduct['sale_price']): ?>
                        <span class="sale-badge">Promoção</span>
                        <?php endif; ?>
                        <button class="add-to-cart-btn" data-product-id="<?php echo $relatedProduct['id']; ?>">
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                    </div>
                    <div class="product-info">
                        <h3><a href="<?php echo SITE_URL; ?>/produto/<?php echo $relatedProduct['slug']; ?>"><?php echo $relatedProduct['name']; ?></a></h3>
                        <div class="product-price">
                            <?php if ($relatedProduct['sale_price']): ?>
                            <span class="original-price"><?php echo formatPrice($relatedProduct['price']); ?></span>
                            <span class="sale-price"><?php echo formatPrice($relatedProduct['sale_price']); ?></span>
                            <?php else: ?>
                            <span class="price"><?php echo formatPrice($relatedProduct['price']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Incluir footer
include_once __DIR__ . '/../includes/footer.php';
?>