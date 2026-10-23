<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Criar Categoria - Admin
 */

// Incluir ficheiros de configuração e base de dados
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';

// Verificar se está logado e é admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login');
}

// Definir título da página
$pageTitle = 'Criar Categoria';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Token de segurança inválido'
        ];
        redirect(SITE_URL . '/admin/categorias/nova');
    }
    
    // Obter dados do formulário
    $name = clean($_POST['name'] ?? '');
    $slug = generateSlug($name);
    $description = clean($_POST['description'] ?? '');
    $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $status = $_POST['status'] ?? 'active';
    
    // Validar campos obrigatórios
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Nome é obrigatório';
    }
    
    // Verificar se slug já existe
    if (dbExists('categories', 'slug = ?', [$slug])) {
        $errors[] = 'Slug já existe';
    }
    
    // Se houver erros, redirecionar
    if (count($errors) > 0) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => implode('<br>', $errors)
        ];
        redirect(SITE_URL . '/admin/categorias/nova');
    }
    
    // Processar upload de imagem
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = vincit_category_upload_fs_dir();
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image = $fileName;
        }
    }
    
    // Inserir categoria
    try {
        $categoryId = dbInsert('categories', [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'image' => $image,
            'parent_id' => $parentId,
            'status' => $status
        ]);
        
        if ($categoryId) {
            $_SESSION['notification'] = [
                'type' => 'success',
                'message' => 'Categoria criada com sucesso!'
            ];
            
            redirect(SITE_URL . '/admin/categorias');
        } else {
            throw new Exception('Erro ao criar categoria');
        }
        
    } catch (Exception $e) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Erro ao criar categoria: ' . $e->getMessage()
        ];
        
        redirect(SITE_URL . '/admin/categorias/nova');
    }
}

// Obter categorias pai
$parentCategories = dbFetchAll("SELECT * FROM categories WHERE parent_id IS NULL AND status = 'active' ORDER BY name");

// Incluir header do admin
include_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Create Category Content -->
<div class="admin-content">
    <div class="admin-header">
        <div class="header-title">
            <h1>Criar Categoria</h1>
            <p>Adicione uma nova categoria à loja</p>
        </div>
        <div class="header-actions">
            <a href="<?php echo SITE_URL; ?>/admin/categorias" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
    
    <form action="<?php echo SITE_URL; ?>/admin/categorias/nova" method="POST" enctype="multipart/form-data" class="category-form">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <div class="form-grid">
            <!-- Main Info -->
            <div class="form-card">
                <div class="card-header">
                    <h2>Informações Principais</h2>
                </div>
                <div class="card-content">
                    <div class="form-group">
                        <label for="name">Nome da Categoria *</label>
                        <input type="text" id="name" name="name" value="<?php echo $_POST['name'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Descrição</label>
                        <textarea id="description" name="description" rows="4"><?php echo $_POST['description'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="parent_id">Categoria Pai</label>
                        <select id="parent_id" name="parent_id">
                            <option value="">Nenhuma (categoria principal)</option>
                            <?php foreach ($parentCategories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo ($_POST['parent_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo $category['name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Estado</label>
                        <select id="status" name="status">
                            <option value="active" <?php echo ($_POST['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Ativo</option>
                            <option value="inactive" <?php echo ($_POST['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inativo</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Image -->
            <div class="form-card">
                <div class="card-header">
                    <h2>Imagem</h2>
                </div>
                <div class="card-content">
                    <div class="form-group">
                        <label for="image">Imagem da Categoria</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <div class="image-preview-container">
                            <img class="image-preview" style="display: none;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Criar Categoria
            </button>
            <a href="<?php echo SITE_URL; ?>/admin/categorias" class="btn btn-outline btn-lg">
                Cancelar
            </a>
        </div>
    </form>
</div>

<?php
// Incluir footer do admin
include_once __DIR__ . '/../includes/admin_footer.php';
?>