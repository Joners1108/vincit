<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Gestão de Produtos - Admin
 */

// Incluir ficheiros de configuração e base de dados
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';

// Verificar se está logado e é admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login');
}

// Definir título da página
$pageTitle = 'Gestão de Produtos';

// Processar eliminação
if (isset($_GET['delete'])) {
    $productId = (int)$_GET['delete'];
    
    // Eliminar produto
    dbDelete('products', 'id = ?', [$productId]);
    
    $_SESSION['notification'] = [
        'type' => 'success',
        'message' => 'Produto eliminado com sucesso!'
    ];
    
    redirect(SITE_URL . '/admin/produtos');
}

// Obter parâmetros de filtro
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Preparar query
$where = "1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND p.name LIKE ?";
    $params[] = "%{$search}%";
}

if (!empty($category)) {
    $where .= " AND p.category_id = ?";
    $params[] = $category;
}

if (!empty($status)) {
    $where .= " AND p.status = ?";
    $params[] = $status;
}

// Contar total de produtos
$totalProducts = dbCount('products p', $where, $params);
$productsPerPage = 20;
$totalPages = ceil($totalProducts / $productsPerPage);
$offset = ($page - 1) * $productsPerPage;

// Obter produtos
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE {$where} 
        ORDER BY p.created_at DESC 
        LIMIT {$productsPerPage} OFFSET {$offset}";

$products = dbFetchAll($sql, $params);

// Obter categorias para filtro
$categories = getCategories();

// Incluir header do admin
include_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Products Content -->
<div class="admin-content">
    <div class="admin-header">
        <div class="header-title">
            <h1>Gestão de Produtos</h1>
            <p><?php echo $totalProducts; ?> produto<?php echo $totalProducts !== 1 ? 's' : ''; ?> encontrado<?php echo $totalProducts !== 1 ? 's' : ''; ?></p>
        </div>
        <div class="header-actions">
            <a href="<?php echo SITE_URL; ?>/admin/produtos/novo" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Produto
            </a>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filters-card">
        <form action="<?php echo SITE_URL; ?>/admin/produtos" method="GET" class="filters-form">
            <div class="filter-group">
                <label for="search">Pesquisar</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Nome do produto...">
            </div>
            
            <div class="filter-group">
                <label for="category">Categoria</label>
                <select id="category" name="category">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo $cat['name']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="status">Estado</label>
                <select id="status" name="status">
                    <option value="">Todos os estados</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Ativo</option>
                    <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inativo</option>
                    <option value="out_of_stock" <?php echo $status === 'out_of_stock' ? 'selected' : ''; ?>>Esgotado</option>
                </select>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="<?php echo SITE_URL; ?>/admin/produtos" class="btn btn-outline">
                    <i class="fas fa-times"></i> Limpar
                </a>
            </div>
        </form>
    </div>
    
    <!-- Products Table -->
    <div class="table-card">
        <div class="table-header">
            <h2>Produtos</h2>
            <div class="table-actions">
                <input type="text" class="table-search" placeholder="Pesquisar na tabela...">
            </div>
        </div>
        
        <div class="table-content">
            <?php if (count($products) > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Imagem</th>
                        <th data-sort="name">Nome</th>
                        <th data-sort="category">Categoria</th>
                        <th data-sort="price">Preço</th>
                        <th data-sort="stock">Stock</th>
                        <th data-sort="status">Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <div class="product-image">
                                <?php if ($product['image']): ?>
                                <img src="<?php echo htmlspecialchars(vincit_product_image_url($product['image'] ?? '')); ?>" alt="<?php echo $product['name']; ?>">
                                <?php else: ?>
                                <div class="image-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <a href="<?php echo SITE_URL; ?>/admin/produtos/editar/<?php echo $product['id']; ?>">
                                <?php echo $product['name']; ?>
                            </a>
                            <?php if ($product['featured']): ?>
                            <span class="featured-badge">Destaque</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $product['category_name'] ?? '-'; ?></td>
                        <td>
                            <?php if ($product['sale_price']): ?>
                            <span class="original-price"><?php echo formatPrice($product['price']); ?></span>
                            <span class="sale-price"><?php echo formatPrice($product['sale_price']); ?></span>
                            <?php else: ?>
                            <?php echo formatPrice($product['price']); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="stock-badge <?php echo $product['stock'] <= 0 ? 'out-of-stock' : ($product['stock'] <= 5 ? 'low-stock' : ''); ?>">
                                <?php echo $product['stock']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $product['status']; ?>">
                                <?php 
                                $statuses = [
                                    'active' => 'Ativo',
                                    'inactive' => 'Inativo',
                                    'out_of_stock' => 'Esgotado'
                                ];
                                echo $statuses[$product['status']] ?? $product['status'];
                                ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?php echo SITE_URL; ?>/admin/produtos/editar/<?php echo $product['id']; ?>" class="btn btn-sm btn-outline" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo SITE_URL; ?>/produto/<?php echo $product['slug']; ?>" target="_blank" class="btn btn-sm btn-outline" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo SITE_URL; ?>/admin/produtos?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger delete-btn" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category; ?>&status=<?php echo $status; ?>" class="pagination-prev">
                    <i class="fas fa-chevron-left"></i> Anterior
                </a>
                <?php endif; ?>
                
                <div class="pagination-numbers">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category; ?>&status=<?php echo $status; ?>" class="pagination-number <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                </div>
                
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category; ?>&status=<?php echo $status; ?>" class="pagination-next">
                    Seguinte <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="no-results">
                <i class="fas fa-box-open"></i>
                <h3>Nenhum produto encontrado</h3>
                <p>Não foram encontrados produtos com os filtros selecionados.</p>
                <a href="<?php echo SITE_URL; ?>/admin/produtos/novo" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Adicionar Produto
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Incluir footer do admin
include_once __DIR__ . '/../includes/admin_footer.php';
?>