<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Editar Produto - Admin
 */

// Incluir ficheiros de configuração e base de dados
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../../includes/product_photos.php';
require_once __DIR__ . '/../../includes/product_sizes_lib.php';

// Verificar se está logado e é admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login');
}

// Obter ID do produto
$productId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$productId) {
    redirect(SITE_URL . '/admin/produtos');
}

// Obter produto (inclui inativos — backoffice)
$product = getProductById($productId, true);

if (!$product) {
    $_SESSION['notification'] = [
        'type' => 'error',
        'message' => 'Produto não encontrado'
    ];
    redirect(SITE_URL . '/admin/produtos');
}

// Definir título da página
$pageTitle = 'Editar Produto: ' . $product['name'];

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Token de segurança inválido'
        ];
        redirect(SITE_URL . '/admin/produtos/editar/' . $productId);
    }
    
    // Obter dados do formulário
    $name = clean($_POST['name'] ?? '');
    $slug = generateSlug($name);
    $description = clean($_POST['description'] ?? '');
    $shortDescription = clean($_POST['short_description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $salePrice = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $brand = clean($_POST['brand'] ?? '');
    $color = clean($_POST['color'] ?? '');
    $material = clean($_POST['material'] ?? '');
    $gender = $_POST['gender'] ?? 'unissex';
    $featured = isset($_POST['featured']);
    $status = $_POST['status'] ?? 'active';
    $metaTitle = clean($_POST['meta_title'] ?? '');
    $metaDescription = clean($_POST['meta_description'] ?? '');
    
    // Validar campos obrigatórios
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Nome é obrigatório';
    }
    
    if ($price <= 0) {
        $errors[] = 'Preço deve ser maior que zero';
    }
    
    // Se houver erros, redirecionar
    if (count($errors) > 0) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => implode('<br>', $errors)
        ];
        redirect(SITE_URL . '/admin/produtos/editar/' . $productId);
    }
    
    $uploadDir = vincit_product_upload_fs_dir();
    $allowedRemove = array_flip(getProductPhotosOrdered($product));
    $removeList = $_POST['remove_photo'] ?? [];
    if (!is_array($removeList)) {
        $removeList = [];
    }
    foreach ($removeList as $toRemove) {
        if (!is_string($toRemove) || $toRemove === '' || !isset($allowedRemove[$toRemove])) {
            continue;
        }
        vincit_try_unlink_uploaded_image($toRemove, 'product');
    }
    $existing = getProductPhotosOrdered($product);
    $existing = array_values(array_filter($existing, static function ($f) use ($removeList) {
        return !in_array($f, $removeList, true);
    }));
    $slots = max(0, VINCIT_MAX_PRODUCT_PHOTOS - count($existing));
    $newFiles = collectUploadedProductPhotos($uploadDir, 'photos', $slots);
    $merged = array_merge($existing, $newFiles);
    $split = splitMainAndGallery($merged);
    $image = $split['image'];
    $galleryString = $split['gallery'];
    
    // Atualizar produto
    try {
        dbUpdate('products', [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'short_description' => $shortDescription,
            'price' => $price,
            'sale_price' => $salePrice,
            'category_id' => $categoryId,
            'brand' => $brand,
            'color' => $color,
            'material' => $material,
            'gender' => $gender,
            'image' => $image,
            'gallery' => $galleryString,
            'featured' => $featured ? 1 : 0,
            'status' => $status,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription
        ], 'id = ?', [$productId]);

        $sizeErr = vincit_save_product_sizes_from_post($productId, $_POST);
        if ($sizeErr !== null) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => $sizeErr,
            ];
            redirect(SITE_URL . '/admin/produtos/editar/' . $productId);
        }
        
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Produto atualizado com sucesso!'
        ];
        
        redirect(SITE_URL . '/admin/produtos');
        
    } catch (Exception $e) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Erro ao atualizar produto: ' . $e->getMessage()
        ];
        
        redirect(SITE_URL . '/admin/produtos/editar/' . $productId);
    }
}

// Obter categorias
$categories = getCategories();

$editProductPhotos = getProductPhotosOrdered($product);

$editSizes = vincit_get_product_sizes($productId);
if (count($editSizes) === 0) {
    $editSizes = [
        ['size_label' => '', 'stock' => 0],
        ['size_label' => '', 'stock' => 0],
    ];
}

// Incluir header do admin
include_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Edit Product Content -->
<div class="admin-content">
    <div class="admin-header">
        <div class="header-title">
            <h1>Editar Produto: <?php echo $product['name']; ?></h1>
            <p>Atualize as informações do produto</p>
        </div>
        <div class="header-actions">
            <a href="<?php echo SITE_URL; ?>/admin/produtos" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <a href="<?php echo SITE_URL; ?>/produto/<?php echo $product['slug']; ?>" target="_blank" class="btn btn-secondary">
                <i class="fas fa-eye"></i> Ver Produto
            </a>
        </div>
    </div>
    
    <form action="<?php echo SITE_URL; ?>/admin/produtos/editar/<?php echo $productId; ?>" method="POST" enctype="multipart/form-data" class="product-form">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <div class="form-grid">
            <!-- Main Info -->
            <div class="form-card">
                <div class="card-header">
                    <h2>Informações Principais</h2>
                </div>
                <div class="card-content">
                    <div class="form-group">
                        <label for="name">Nome do Produto *</label>
                        <input type="text" id="name" name="name" value="<?php echo $product['name']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Descrição</label>
                        <textarea id="description" name="description" rows="5"><?php echo $product['description']; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="short_description">Descrição Curta</label>
                        <textarea id="short_description" name="short_description" rows="2"><?php echo $product['short_description']; ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Preço *</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="sale_price">Preço de Promoção</label>
                            <input type="number" id="sale_price" name="sale_price" step="0.01" min="0" value="<?php echo $product['sale_price']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Tamanhos e stock *</label>
                        <p style="font-size:0.9rem;color:#666;margin:0 0 0.5rem;">Uma linha por tamanho. O stock total do produto é a <strong>soma</strong> das quantidades (atual: <?php echo (int) $product['stock']; ?>).</p>
                        <table class="admin-size-table" style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="text-align:left;border-bottom:1px solid #ddd;">
                                    <th style="padding:8px;">Tamanho</th>
                                    <th style="padding:8px;width:120px;">Quantidade</th>
                                    <th style="width:40px;"></th>
                                </tr>
                            </thead>
                            <tbody id="admin-size-rows">
                                <?php foreach ($editSizes as $row): ?>
                                <tr>
                                    <td style="padding:6px 8px;"><input type="text" name="size_label[]" value="<?php echo htmlspecialchars($row['size_label']); ?>" placeholder="ex. M ou 42" style="width:100%;"></td>
                                    <td style="padding:6px 8px;"><input type="number" name="size_stock[]" min="0" value="<?php echo isset($row['stock']) ? (int) $row['stock'] : 0; ?>" style="width:100%;"></td>
                                    <td style="padding:6px;"><button type="button" class="btn btn-sm admin-remove-size" title="Remover linha">×</button></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-outline btn-sm" id="admin-add-size-row" style="margin-top:0.5rem;">+ Adicionar tamanho</button>
                    </div>
                </div>
            </div>
            
            <!-- Category & Details -->
            <div class="form-card">
                <div class="card-header">
                    <h2>Categoria e Detalhes</h2>
                </div>
                <div class="card-content">
                    <div class="form-group">
                        <label for="category_id">Categoria</label>
                        <select id="category_id" name="category_id">
                            <option value="">Selecione uma categoria</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo $category['name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="brand">Marca</label>
                            <input type="text" id="brand" name="brand" value="<?php echo $product['brand']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="gender">Género</label>
                            <select id="gender" name="gender">
                                <option value="unissex" <?php echo $product['gender'] === 'unissex' ? 'selected' : ''; ?>>Unissex</option>
                                <option value="homem" <?php echo $product['gender'] === 'homem' ? 'selected' : ''; ?>>Homem</option>
                                <option value="mulher" <?php echo $product['gender'] === 'mulher' ? 'selected' : ''; ?>>Mulher</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="color">Cores</label>
                            <input type="text" id="color" name="color" placeholder="Preto, Branco, Azul" value="<?php echo htmlspecialchars($product['color'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="material">Material</label>
                        <input type="text" id="material" name="material" value="<?php echo $product['material']; ?>">
                    </div>
                </div>
            </div>
            
            <!-- Images -->
            <div class="form-card">
                <div class="card-header">
                    <h2>Fotografias</h2>
                </div>
                <div class="card-content">
                    <?php if (!empty($editProductPhotos)): ?>
                    <div class="form-group">
                        <label>Fotos atuais (ordem: 1.ª = capa)</label>
                        <div class="product-photos-edit-list">
                            <?php foreach ($editProductPhotos as $idx => $photoFile): ?>
                            <div class="product-photo-edit-item">
                                <img src="<?php echo htmlspecialchars(vincit_product_image_url($photoFile)); ?>" alt="">
                                <label class="checkbox-label product-photo-remove-label">
                                    <input type="checkbox" name="remove_photo[]" value="<?php echo htmlspecialchars($photoFile); ?>">
                                    <span>Remover</span>
                                </label>
                                <?php if ($idx === 0): ?>
                                <span class="product-photo-cover-badge">Capa</span>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="photos">Adicionar fotos</label>
                        <input type="file" id="photos" name="photos[]" accept="image/*" multiple>
                        <small>Máximo <strong>3</strong> fotos no total. Novas imagens ficam a seguir às atuais; a primeira da lista é a capa.</small>
                    </div>
                </div>
            </div>
            
            <!-- SEO & Status -->
            <div class="form-card">
                <div class="card-header">
                    <h2>SEO e Estado</h2>
                </div>
                <div class="card-content">
                    <div class="form-group">
                        <label for="meta_title">Meta Título</label>
                        <input type="text" id="meta_title" name="meta_title" value="<?php echo $product['meta_title']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="meta_description">Meta Descrição</label>
                        <textarea id="meta_description" name="meta_description" rows="3"><?php echo $product['meta_description']; ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Estado</label>
                            <select id="status" name="status">
                                <option value="active" <?php echo $product['status'] === 'active' ? 'selected' : ''; ?>>Ativo</option>
                                <option value="inactive" <?php echo $product['status'] === 'inactive' ? 'selected' : ''; ?>>Inativo</option>
                                <option value="out_of_stock" <?php echo $product['status'] === 'out_of_stock' ? 'selected' : ''; ?>>Esgotado</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="featured" <?php echo $product['featured'] ? 'checked' : ''; ?>>
                                <span>Produto em Destaque</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Guardar Alterações
            </button>
            <a href="<?php echo SITE_URL; ?>/admin/produtos" class="btn btn-outline btn-lg">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
(function () {
  var tb = document.getElementById('admin-size-rows');
  var add = document.getElementById('admin-add-size-row');
  if (add && tb) {
    add.addEventListener('click', function () {
      var tr = document.createElement('tr');
      tr.innerHTML = '<td style="padding:6px 8px;"><input type="text" name="size_label[]" value="" placeholder="ex. M" style="width:100%;"></td>' +
        '<td style="padding:6px 8px;"><input type="number" name="size_stock[]" min="0" value="0" style="width:100%;"></td>' +
        '<td style="padding:6px;"><button type="button" class="btn btn-sm admin-remove-size" title="Remover linha">×</button></td>';
      tb.appendChild(tr);
    });
  }
  document.addEventListener('click', function (e) {
    if (e.target.closest('.admin-remove-size')) {
      var tr = e.target.closest('tr');
      if (tr && tb && tb.querySelectorAll('tr').length > 1) {
        tr.remove();
      }
    }
  });
})();
</script>
<?php
// Incluir footer do admin
include_once __DIR__ . '/../includes/admin_footer.php';
?>