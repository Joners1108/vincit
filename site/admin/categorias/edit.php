<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Editar Categoria - Admin
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login');
}

$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$categoryId) {
    redirect(SITE_URL . '/admin/categorias');
}

$category = dbFetch('SELECT * FROM categories WHERE id = ?', [$categoryId]);
if (!$category) {
    $_SESSION['notification'] = [
        'type' => 'error',
        'message' => 'Categoria não encontrada',
    ];
    redirect(SITE_URL . '/admin/categorias');
}

$pageTitle = 'Editar Categoria: ' . $category['name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Token de segurança inválido',
        ];
        redirect(SITE_URL . '/admin/categorias/editar/' . $categoryId);
    }

    $name = clean($_POST['name'] ?? '');
    $slug = generateSlug($name);
    $description = clean($_POST['description'] ?? '');
    $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $status = $_POST['status'] ?? 'active';

    $errors = [];
    if (empty($name)) {
        $errors[] = 'Nome é obrigatório';
    }
    if ($parentId === $categoryId) {
        $errors[] = 'A categoria não pode ser pai de si mesma';
    }

    $slugRow = dbFetch('SELECT id FROM categories WHERE slug = ? AND id != ?', [$slug, $categoryId]);
    if ($slugRow) {
        $errors[] = 'Slug já existe';
    }

    if (count($errors) > 0) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => implode('<br>', $errors),
        ];
        redirect(SITE_URL . '/admin/categorias/editar/' . $categoryId);
    }

    $image = $category['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = vincit_category_upload_fs_dir();
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            if (!empty($category['image'])) {
                vincit_try_unlink_uploaded_image((string) $category['image'], 'category');
            }
            $image = $fileName;
        }
    }

    try {
        $ok = dbUpdate(
            'categories',
            [
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'image' => $image,
                'parent_id' => $parentId,
                'status' => $status,
            ],
            'id = ?',
            [$categoryId]
        );

        if ($ok !== false) {
            $_SESSION['notification'] = [
                'type' => 'success',
                'message' => 'Categoria atualizada com sucesso!',
            ];
            redirect(SITE_URL . '/admin/categorias');
        }
        throw new Exception('Erro ao atualizar');
    } catch (Exception $e) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Erro ao atualizar categoria: ' . $e->getMessage(),
        ];
        redirect(SITE_URL . '/admin/categorias/editar/' . $categoryId);
    }
}

$parentCategories = dbFetchAll(
    "SELECT * FROM categories WHERE parent_id IS NULL AND status = 'active' AND id != ? ORDER BY name",
    [$categoryId]
);

include_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-content">
    <div class="admin-header">
        <div class="header-title">
            <h1>Editar Categoria</h1>
            <p>Atualize os dados da categoria</p>
        </div>
        <div class="header-actions">
            <a href="<?php echo SITE_URL; ?>/admin/categorias" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <form action="<?php echo SITE_URL; ?>/admin/categorias/editar/<?php echo (int)$categoryId; ?>" method="POST" enctype="multipart/form-data" class="category-form">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

        <div class="form-grid">
            <div class="form-card">
                <div class="card-header">
                    <h2>Informações Principais</h2>
                </div>
                <div class="card-content">
                    <div class="form-group">
                        <label for="name">Nome da Categoria *</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Descrição</label>
                        <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="parent_id">Categoria Pai</label>
                        <select id="parent_id" name="parent_id">
                            <option value="">Nenhuma (categoria principal)</option>
                            <?php foreach ($parentCategories as $pc): ?>
                            <option value="<?php echo (int)$pc['id']; ?>" <?php echo (int)($category['parent_id'] ?? 0) === (int)$pc['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($pc['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Estado</label>
                        <select id="status" name="status">
                            <option value="active" <?php echo ($category['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Ativo</option>
                            <option value="inactive" <?php echo ($category['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inativo</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-card">
                <div class="card-header">
                    <h2>Imagem</h2>
                </div>
                <div class="card-content">
                    <?php if (!empty($category['image'])): ?>
                    <p class="form-group">Imagem atual: <img src="<?php echo htmlspecialchars(vincit_category_image_url($category['image'])); ?>" alt="" style="max-width:120px;display:block;margin-top:0.5rem;"></p>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="image">Nova imagem (opcional)</label>
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
                <i class="fas fa-save"></i> Guardar alterações
            </button>
            <a href="<?php echo SITE_URL; ?>/admin/categorias" class="btn btn-outline btn-lg">Cancelar</a>
        </div>
    </form>
</div>

<?php
include_once __DIR__ . '/../includes/admin_footer.php';
