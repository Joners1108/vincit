<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Visualizar Utilizador - Admin
 */

// Incluir ficheiros de configuração e base de dados
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';

// Verificar se está logado e é admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login');
}

// Obter ID do utilizador
$userId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$userId) {
    redirect(SITE_URL . '/admin/utilizadores');
}

// Obter utilizador
$user = dbFetch("SELECT * FROM users WHERE id = ?", [$userId]);

if (!$user) {
    $_SESSION['notification'] = [
        'type' => 'error',
        'message' => 'Utilizador não encontrado'
    ];
    redirect(SITE_URL . '/admin/utilizadores');
}

// Obter encomendas do utilizador
$orders = getUserOrders($userId);

// Obter estatísticas do utilizador
$totalOrders = count($orders);
$totalSpent = 0;
foreach ($orders as $order) {
    if ($order['payment_status'] === 'paid') {
        $totalSpent += $order['total'];
    }
}

// Definir título da página
$pageTitle = 'Utilizador: ' . $user['name'];

// Processar atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Validar CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Token de segurança inválido'
        ];
        redirect(SITE_URL . '/admin/utilizadores/' . $userId);
    }
    
    $action = $_POST['action'];
    
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
        
        redirect(SITE_URL . '/admin/utilizadores/' . $userId);
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
        
        redirect(SITE_URL . '/admin/utilizadores/' . $userId);
    }
}

// Incluir header do admin
include_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- User View Content -->
<div class="admin-content">
    <div class="admin-header">
        <div class="header-title">
            <h1><?php echo $user['name']; ?></h1>
            <p>Detalhes do utilizador</p>
        </div>
        <div class="header-actions">
            <a href="<?php echo SITE_URL; ?>/admin/utilizadores" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
    
    <div class="user-view-grid">
        <!-- User Details -->
        <div class="user-details-card">
            <div class="card-header">
                <h2>Dados do Utilizador</h2>
            </div>
            <div class="card-content">
                <div class="user-info">
                    <div class="info-row">
                        <span class="info-label">Nome:</span>
                        <span class="info-value"><?php echo $user['name']; ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo $user['email']; ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Telefone:</span>
                        <span class="info-value"><?php echo $user['phone'] ?? '-'; ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Morada:</span>
                        <span class="info-value"><?php echo $user['address'] ?? '-'; ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Cidade:</span>
                        <span class="info-value"><?php echo $user['city'] ?? '-'; ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Código Postal:</span>
                        <span class="info-value"><?php echo $user['postal_code'] ?? '-'; ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">País:</span>
                        <span class="info-value"><?php echo $user['country'] ?? '-'; ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Função:</span>
                        <span class="info-value">
                            <span class="role-badge role-<?php echo $user['role']; ?>">
                                <?php 
                                $roles = [
                                    'customer' => 'Cliente',
                                    'admin' => 'Administrador'
                                ];
                                echo $roles[$user['role']] ?? $user['role'];
                                ?>
                            </span>
                        </span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Estado:</span>
                        <span class="info-value">
                            <span class="status-badge status-<?php echo $user['status']; ?>">
                                <?php 
                                $statuses = [
                                    'active' => 'Ativo',
                                    'inactive' => 'Inativo'
                                ];
                                echo $statuses[$user['status']] ?? $user['status'];
                                ?>
                            </span>
                        </span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Data de Registo:</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- User Stats -->
        <div class="user-stats-card">
            <div class="card-header">
                <h2>Estatísticas</h2>
            </div>
            <div class="card-content">
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $totalOrders; ?></span>
                        <span class="stat-label">Encomendas</span>
                    </div>
                    
                    <div class="stat-item">
                        <span class="stat-number"><?php echo formatPrice($totalSpent); ?></span>
                        <span class="stat-label">Total Gasto</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Update Status -->
        <div class="update-status-card">
            <div class="card-header">
                <h2>Atualizar Estado</h2>
            </div>
            <div class="card-content">
                <form action="<?php echo SITE_URL; ?>/admin/utilizadores/<?php echo $userId; ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="update_status">
                    
                    <div class="form-group">
                        <label for="status">Estado</label>
                        <select id="status" name="status" required>
                            <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Ativo</option>
                            <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inativo</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Atualizar Estado
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Update Role -->
        <div class="update-role-card">
            <div class="card-header">
                <h2>Atualizar Função</h2>
            </div>
            <div class="card-content">
                <form action="<?php echo SITE_URL; ?>/admin/utilizadores/<?php echo $userId; ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="update_role">
                    
                    <div class="form-group">
                        <label for="role">Função</label>
                        <select id="role" name="role" required>
                            <option value="customer" <?php echo $user['role'] === 'customer' ? 'selected' : ''; ?>>Cliente</option>
                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Atualizar Função
                    </button>
                </form>
            </div>
        </div>
        
        <!-- User Orders -->
        <div class="user-orders-card">
            <div class="card-header">
                <h2>Encomendas do Utilizador</h2>
            </div>
            <div class="card-content">
                <?php if (count($orders) > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Data</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <a href="<?php echo SITE_URL; ?>/admin/encomendas/editar/<?php echo $order['id']; ?>">
                                    <?php echo $order['order_number']; ?>
                                </a>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                            <td><?php echo formatPrice($order['total']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php 
                                    $statuses = [
                                        'pending' => 'Pendente',
                                        'processing' => 'Em processamento',
                                        'shipped' => 'Enviado',
                                        'delivered' => 'Entregue',
                                        'cancelled' => 'Cancelado'
                                    ];
                                    echo $statuses[$order['status']] ?? $order['status'];
                                    ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo SITE_URL; ?>/admin/encomendas/editar/<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>Este utilizador ainda não fez nenhuma encomenda.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir footer do admin
include_once __DIR__ . '/../includes/admin_footer.php';
?>