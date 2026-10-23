<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Página da Loja
 */

// Incluir ficheiros de configuração e base de dados
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';

// Definir título da página
$pageTitle = 'Loja';

// Obter parâmetros de filtro
$categorySlug = $_GET['category'] ?? null;
$gender = $_GET['gender'] ?? null;
$search = $_GET['search'] ?? null;
$order = $_GET['order'] ?? null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Preparar opções de filtro
$options = [
    'page' => $page,
    'order' => $order
];

// Filtro por categoria
if ($categorySlug) {
    $category = dbFetch("SELECT id FROM categories WHERE slug = ?", [$categorySlug]);
    if ($category) {
        $options['category_id'] = $category['id'];
        $pageTitle = $categorySlug;
    }
}

// Filtro por género
if ($gender) {
    $options['gender'] = $gender;
    $pageTitle = $gender === 'homem' ? 'Homem' : 'Mulher';
}

// Filtro por pesquisa
if ($search) {
    $options['search'] = $search;
    $pageTitle = 'Pesquisa: ' . $search;
}

// Obter produtos
$products = getProducts($options);
$totalProducts = countProducts($options);
$totalPages = ceil($totalProducts / PRODUCTS_PER_PAGE);

// Obter categorias para o menu
$categories = getCategories(true);

// Incluir header
include_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<section class="breadcrumb">
    <div class="container">
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb-list">
                <li><a href="<?php echo SITE_URL; ?>">Início</a></li>
                <li><a href="<?php echo SITE_URL; ?>/loja">Loja</a></li>
                <?php if ($categorySlug): ?>
                <li><a href="<?php echo SITE_URL; ?>/loja/categoria/<?php echo $categorySlug; ?>"><?php echo ucfirst($categorySlug); ?></a></li>
                <?php endif; ?>
                <?php if ($gender): ?>
                <li><a href="<?php echo SITE_URL; ?>/loja/<?php echo $gender; ?>"><?php echo ucfirst($gender); ?></a></li>
                <?php endif; ?>
                <?php if ($search): ?>
                <li>Pesquisa: <?php echo htmlspecialchars($search); ?></li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</section>

<!-- Shop Section -->
<section class="shop-section">
    <div class="container">
        <div class="shop-layout">
            <!-- Sidebar -->
            <aside class="shop-sidebar">
                <!-- Categorias -->
                <div class="sidebar-widget">
                    <h3 class="widget-title">Categorias</h3>
                    <ul class="category-list">
                        <li><a href="<?php echo SITE_URL; ?>/loja" class="<?php echo !$categorySlug && !$gender ? 'active' : ''; ?>">Todos os Produtos</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/loja/homem" class="<?php echo $gender === 'homem' ? 'active' : ''; ?>">Homem</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/loja/mulher" class="<?php echo $gender === 'mulher' ? 'active' : ''; ?>">Mulher</a></li>
                        <?php foreach ($categories as $category): ?>
                        <li><a href="<?php echo SITE_URL; ?>/loja/categoria/<?php echo $category['slug']; ?>" class="<?php echo $categorySlug === $category['slug'] ? 'active' : ''; ?>"><?php echo $category['name']; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Filtros -->
                <div class="sidebar-widget">
                    <h3 class="widget-title">Ordenar Por</h3>
                    <form action="<?php echo SITE_URL; ?>/loja" method="GET" class="filter-form">
                        <?php if ($categorySlug): ?>
                        <input type="hidden" name="category" value="<?php echo $categorySlug; ?>">
                        <?php endif; ?>
                        <?php if ($gender): ?>
                        <input type="hidden" name="gender" value="<?php echo $gender; ?>">
                        <?php endif; ?>
                        <?php if ($search): ?>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <?php endif; ?>
                        
                        <select name="order" onchange="this.form.submit()">
                            <option value="">Padrão</option>
                            <option value="price_asc" <?php echo $order === 'price_asc' ? 'selected' : ''; ?>>Preço: Menor para Maior</option>
                            <option value="price_desc" <?php echo $order === 'price_desc' ? 'selected' : ''; ?>>Preço: Maior para Menor</option>
                            <option value="name_asc" <?php echo $order === 'name_asc' ? 'selected' : ''; ?>>Nome: A-Z</option>
                            <option value="name_desc" <?php echo $order === 'name_desc' ? 'selected' : ''; ?>>Nome: Z-A</option>
                        </select>
                    </form>
                </div>
            </aside>
            
            <!-- Main Content -->
            <div class="shop-main">
                <!-- Header -->
                <div class="shop-header">
                    <h1 class="shop-title"><?php echo $pageTitle; ?></h1>
                    <div class="shop-meta">
                        <span class="product-count"><?php echo $totalProducts; ?> produto<?php echo $totalProducts !== 1 ? 's' : ''; ?> encontrado<?php echo $totalProducts !== 1 ? 's' : ''; ?></span>
                    </div>
                </div>
                
                <!-- Products Grid -->
                <?php if (count($products) > 0): ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
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
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $categorySlug ? '&category=' . $categorySlug : ''; ?><?php echo $gender ? '&gender=' . $gender : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $order ? '&order=' . $order : ''; ?>" class="pagination-prev">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>
                    <?php endif; ?>
                    
                    <div class="pagination-numbers">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo $categorySlug ? '&category=' . $categorySlug : ''; ?><?php echo $gender ? '&gender=' . $gender : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $order ? '&order=' . $order : ''; ?>" class="pagination-number <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                        <?php endfor; ?>
                    </div>
                    
                    <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $categorySlug ? '&category=' . $categorySlug : ''; ?><?php echo $gender ? '&gender=' . $gender : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $order ? '&order=' . $order : ''; ?>" class="pagination-next">
                        Seguinte <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="no-products">
                    <i class="fas fa-box-open"></i>
                    <h2>Nenhum produto encontrado</h2>
                    <p>Não foram encontrados produtos com os filtros selecionados.</p>
                    <a href="<?php echo SITE_URL; ?>/loja" class="btn btn-primary">Ver Todos os Produtos</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
// Incluir footer
include_once __DIR__ . '/../includes/footer.php';
?>