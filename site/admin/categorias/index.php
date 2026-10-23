<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Gestão de Categorias - Admin
 */

// Incluir ficheiros de configuração e base de dados
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';

// Verificar se está logado e é admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login');
}

// Definir título da página
$pageTitle = 'Gestão de Categorias';

// Processar eliminação
if (isset($_GET['delete'])) {
    $categoryId = (int)$_GET['delete'];
    
    // Verificar se categoria tem produtos
    $productCount = dbCount('products', 'category_id = ?', [$categoryId]);
    
    if ($productCount > 0) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Não é possível eliminar categoria com produtos associados'
        ];
    } else {
        // Eliminar categoria
        dbDelete('categories', 'id = ?', [$categoryId]);
        
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Categoria eliminada com sucesso!'
        ];
    }
    
    redirect(SITE_URL . '/admin/categorias');
}

// Obter parâmetros de filtro
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Preparar query
$where = "1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (c.name LIKE ? OR c.slug LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($status)) {
    $where .= " AND c.status = ?";
    $params[] = $status;
}

// Contar total de categorias
$totalCategories = dbCount('categories c', $where, $params);
$categoriesPerPage = 20;
$totalPages = ceil($totalCategories / $categoriesPerPage);
$offset = ($page - 1) * $categoriesPerPage;

// Obter categorias
$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count,
        pc.name as parent_name
        FROM categories c 
        LEFT JOIN categories pc ON c.parent_id = pc.id 
        WHERE {$where} 
        ORDER BY c.name ASC 
        LIMIT {$categoriesPerPage} OFFSET {$offset}";

$categories = dbFetchAll($sql, $params);

// Obter categorias pai para filtro
$parentCategories = dbFetchAll("SELECT * FROM categories WHERE parent_id IS NULL AND status = 'active' ORDER BY name");

// Incluir header do admin
include_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Categories Content -->
<div class="admin-content">
    <div class="admin-header">
        <div class="header-title">
            <h1>Gestão de Categorias</h1>
            <p><?php echo $totalCategories; ?> categoria<?php echo $totalCategories !== 1 ? 's' : ''; ?> encontrada<?php echo $totalCategories !== 1 ? 's' : ''; ?></p>
        </div>
        <div class="header-actions">
            <a href="<?php echo SITE_URL; ?>/admin/categorias/nova" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nova Categoria
            </a>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filters-card">
        <form action="<?php echo SITE_URL; ?>/admin/categorias" method="GET" class="filters-form">
            <div class="filter-group">
                <label for="search">Pesquisar</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Nome ou slug...">
            </div>
            
            <div class="filter-group">
                <label for="status">Estado</label>
                <select id="status" name="status">
                    <option value="">Todos os estados</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Ativo</option>
                    <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inativo</option>
                </select>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="<?php echo SITE_URL; ?>/admin/categorias" class="btn btn-outline">
                    <i class="fas fa-times"></i> Limpar
                </a>
            </div>
        </form>
    </div>
    
    <!-- Categories Table -->
    <div class="table-card">
        <div class="table-header">
            <h2>Categorias</h2>
            <div class="table-actions">
                <input type="text" class="table-search" placeholder="Pesquisar na tabela...">
            </div>
        </div>
        
        <div class="table-content">
            <?php if (count($categories) > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Imagem</th>
                        <th data-sort="name">Nome</th>
                        <th data-sort="slug">Slug</th>
                        <th data-sort="parent">Categoria Pai</th>
                        <th data-sort="products">Produtos</th>
                        <th data-sort="status">Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td>
                            <div class="category-image">
                                <?php if ($category['image']): ?>
                                <img src="<?php echo htmlspecialchars(vincit_category_image_url($category['image'] ?? '')); ?>" alt="<?php echo $category['name']; ?>">
                                <?php else: ?>
                                <div class="image-placeholder">
                                    <i class="fas fa-folder"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <a href="<?php echo SITE_URL; ?>/admin/categorias/editar/<?php echo $category['id']; ?>">
                                <?php echo $category['name']; ?>
                            </a>
                        </td>
                        <td><?php echo $category['slug']; ?></td>
                        <td><?php echo $category['parent_name'] ?? '-'; ?></td>
                        <td>
                            <span class="product-count"><?php echo $category['product_count']; ?></span>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $category['status']; ?>">
                                <?php 
                                $statuses = [
                                    'active' => 'Ativo',
                                    'inactive' => 'Inativo'
                                ];
                                echo $statuses[$category['status']] ?? $category['status'];
                                ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?php echo SITE_URL; ?>/admin/categorias/editar/<?php echo $category['id']; ?>" class="btn btn-sm btn-outline" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo SITE_URL; ?>/loja/categoria/<?php echo $category['slug']; ?>" target="_blank" class="btn btn-sm btn-outline" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo SITE_URL; ?>/admin/categorias?delete=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger delete-btn" title="Eliminar">
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
                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>" class="pagination-prev">
                    <i class="fas fa-chevron-left"></i> Anterior
                </a>
                <?php endif; ?>
                
                <div class="pagination-numbers">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>" class="pagination-number <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                </div>
                
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>" class="pagination-next">
                    Seguinte <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="no-results">
                <i class="fas fa-folder-open"></i>
                <h3>Nenhuma categoria encontrada</h3>
                <p>Não foram encontradas categorias com os filtros selecionados.</p>
                <a href="<?php echo SITE_URL; ?>/admin/categorias/nova" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Adicionar Categoria
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