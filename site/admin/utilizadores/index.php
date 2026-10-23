<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Gestão de Utilizadores - Admin
 */

// Incluir ficheiros de configuração e base de dados
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';

// Verificar se está logado e é admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login');
}

// Definir título da página
$pageTitle = 'Gestão de Utilizadores';

// Processar atualização de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Validar CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Token de segurança inválido'
        ];
        redirect(SITE_URL . '/admin/utilizadores');
    }
    
    $action = $_POST['action'];
    $userId = (int)($_POST['user_id'] ?? 0);
    
    if ($action === 'update_status') {
        $status = $_POST['status'] ?? '';
        
        // Atualizar utilizador
        dbUpdate('users', [
            'status' => $status
        ], 'id = ?', [$userId]);
        
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Estado do utilizador atualizado!'
        ];
        
        redirect(SITE_URL . '/admin/utilizadores');
    }
    
    if ($action === 'update_role') {
        $role = $_POST['role'] ?? '';
        
        // Atualizar utilizador
        dbUpdate('users', [
            'role' => $role
        ], 'id = ?', [$userId]);
        
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Função do utilizador atualizada!'
        ];
        
        redirect(SITE_URL . '/admin/utilizadores');
    }
}

// Obter parâmetros de filtro
$search = $_GET['search'] ?? '';
$role = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Preparar query
$where = "1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (u.name LIKE ? OR u.email LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($role)) {
    $where .= " AND u.role = ?";
    $params[] = $role;
}

if (!empty($status)) {
    $where .= " AND u.status = ?";
    $params[] = $status;
}

// Contar total de utilizadores
$totalUsers = dbCount('users u', $where, $params);
$usersPerPage = 20;
$totalPages = ceil($totalUsers / $usersPerPage);
$offset = ($page - 1) * $usersPerPage;

// Obter utilizadores
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as total_orders,
        (SELECT SUM(total) FROM orders WHERE user_id = u.id AND payment_status = 'paid') as total_spent
        FROM users u 
        WHERE {$where} 
        ORDER BY u.created_at DESC 
        LIMIT {$usersPerPage} OFFSET {$offset}";

$users = dbFetchAll($sql, $params);

// Incluir header do admin
include_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Users Content -->
<div class="admin-content">
    <div class="admin-header">
        <div class="header-title">
            <h1>Gestão de Utilizadores</h1>
            <p><?php echo $totalUsers; ?> utilizador<?php echo $totalUsers !== 1 ? 'es' : ''; ?> encontrado<?php echo $totalUsers !== 1 ? 's' : ''; ?></p>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filters-card">
        <form action="<?php echo SITE_URL; ?>/admin/utilizadores" method="GET" class="filters-form">
            <div class="filter-group">
                <label for="search">Pesquisar</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Nome ou email...">
            </div>
            
            <div class="filter-group">
                <label for="role">Função</label>
                <select id="role" name="role">
                    <option value="">Todas as funções</option>
                    <option value="customer" <?php echo $role === 'customer' ? 'selected' : ''; ?>>Cliente</option>
                    <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                </select>
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
                <a href="<?php echo SITE_URL; ?>/admin/utilizadores" class="btn btn-outline">
                    <i class="fas fa-times"></i> Limpar
                </a>
            </div>
        </form>
    </div>
    
    <!-- Users Table -->
    <div class="table-card">
        <div class="table-header">
            <h2>Utilizadores</h2>
            <div class="table-actions">
                <input type="text" class="table-search" placeholder="Pesquisar na tabela...">
            </div>
        </div>
        
        <div class="table-content">
            <?php if (count($users) > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th data-sort="name">Nome</th>
                        <th data-sort="email">Email</th>
                        <th data-sort="role">Função</th>
                        <th data-sort="status">Estado</th>
                        <th data-sort="orders">Encomendas</th>
                        <th data-sort="spent">Total Gasto</th>
                        <th data-sort="date">Data de Registo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <a href="<?php echo SITE_URL; ?>/admin/utilizadores/<?php echo $user['id']; ?>">
                                <?php echo $user['name']; ?>
                            </a>
                        </td>
                        <td><?php echo $user['email']; ?></td>
                        <td>
                            <span class="role-badge role-<?php echo $user['role']; ?>">
                                <?php 
                                $roles = [
                                    'customer' => 'Cliente',
                                    'admin' => 'Administrador'
                                ];
                                echo $roles[$user['role']] ?? $user['role'];
                                ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $user['status']; ?>">
                                <?php 
                                $statuses = [
                                    'active' => 'Ativo',
                                    'inactive' => 'Inativo'
                                ];
                                echo $statuses[$user['status']] ?? $user['status'];
                                ?>
                            </span>
                        </td>
                        <td><?php echo $user['total_orders']; ?></td>
                        <td><?php echo formatPrice($user['total_spent'] ?? 0); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?php echo SITE_URL; ?>/admin/utilizadores/<?php echo $user['id']; ?>" class="btn btn-sm btn-outline" title="Ver Detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline" title="Atualizar Estado" onclick="openStatusModal(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline" title="Atualizar Função" onclick="openRoleModal(<?php echo $user['id']; ?>, '<?php echo $user['role']; ?>')">
                                    <i class="fas fa-user-cog"></i>
                                </button>
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
                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $role; ?>&status=<?php echo $status; ?>" class="pagination-prev">
                    <i class="fas fa-chevron-left"></i> Anterior
                </a>
                <?php endif; ?>
                
                <div class="pagination-numbers">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $role; ?>&status=<?php echo $status; ?>" class="pagination-number <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                </div>
                
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $role; ?>&status=<?php echo $status; ?>" class="pagination-next">
                    Seguinte <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="no-results">
                <i class="fas fa-users"></i>
                <h3>Nenhum utilizador encontrado</h3>
                <p>Não foram encontrados utilizadores com os filtros selecionados.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Status Modal -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Atualizar Estado do Utilizador</h2>
            <button type="button" class="modal-close" onclick="closeStatusModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="<?php echo SITE_URL; ?>/admin/utilizadores" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="user_id" id="modal_status_user_id">
            
            <div class="modal-body">
                <div class="form-group">
                    <label for="modal_status">Estado</label>
                    <select id="modal_status" name="status" required>
                        <option value="active">Ativo</option>
                        <option value="inactive">Inativo</option>
                    </select>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeStatusModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Atualizar</button>
            </div>
        </form>
    </div>
</div>

<!-- Role Modal -->
<div id="roleModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Atualizar Função do Utilizador</h2>
            <button type="button" class="modal-close" onclick="closeRoleModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="<?php echo SITE_URL; ?>/admin/utilizadores" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="update_role">
            <input type="hidden" name="user_id" id="modal_role_user_id">
            
            <div class="modal-body">
                <div class="form-group">
                    <label for="modal_role">Função</label>
                    <select id="modal_role" name="role" required>
                        <option value="customer">Cliente</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeRoleModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Atualizar</button>
            </div>
        </form>
    </div>
</div>

<script>
function openStatusModal(userId, status) {
    document.getElementById('modal_status_user_id').value = userId;
    document.getElementById('modal_status').value = status;
    document.getElementById('statusModal').classList.add('modal--open');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.remove('modal--open');
}

function openRoleModal(userId, role) {
    document.getElementById('modal_role_user_id').value = userId;
    document.getElementById('modal_role').value = role;
    document.getElementById('roleModal').classList.add('modal--open');
}

function closeRoleModal() {
    document.getElementById('roleModal').classList.remove('modal--open');
}

document.addEventListener('click', function (event) {
    var statusModal = document.getElementById('statusModal');
    var roleModal = document.getElementById('roleModal');
    if (event.target === statusModal) closeStatusModal();
    if (event.target === roleModal) closeRoleModal();
});
</script>

<?php
// Incluir footer do admin
include_once __DIR__ . '/../includes/admin_footer.php';
?>