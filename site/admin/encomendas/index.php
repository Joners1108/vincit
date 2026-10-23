<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Gestão de Encomendas - Admin
 */

// Incluir ficheiros de configuração e base de dados
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';

// Verificar se está logado e é admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login');
}

// Definir título da página
$pageTitle = 'Gestão de Encomendas';

// Obter parâmetros de filtro
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$paymentStatus = $_GET['payment_status'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Preparar query
$where = "1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (o.order_number LIKE ? OR o.shipping_name LIKE ? OR o.shipping_email LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($status)) {
    $where .= " AND o.status = ?";
    $params[] = $status;
}

if (!empty($paymentStatus)) {
    $where .= " AND o.payment_status = ?";
    $params[] = $paymentStatus;
}

// Contar total de encomendas
$totalOrders = dbCount('orders o', $where, $params);
$ordersPerPage = 20;
$totalPages = ceil($totalOrders / $ordersPerPage);
$offset = ($page - 1) * $ordersPerPage;

// Obter encomendas
$sql = "SELECT o.*, u.name as user_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE {$where} 
        ORDER BY o.created_at DESC 
        LIMIT {$ordersPerPage} OFFSET {$offset}";

$orders = dbFetchAll($sql, $params);

// Incluir header do admin
include_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Orders Content -->
<div class="admin-content">
    <div class="admin-header">
        <div class="header-title">
            <h1>Gestão de Encomendas</h1>
            <p><?php echo $totalOrders; ?> encomenda<?php echo $totalOrders !== 1 ? 's' : ''; ?> encontrada<?php echo $totalOrders !== 1 ? 's' : ''; ?></p>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filters-card">
        <form action="<?php echo SITE_URL; ?>/admin/encomendas" method="GET" class="filters-form">
            <div class="filter-group">
                <label for="search">Pesquisar</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Número, nome ou email...">
            </div>
            
            <div class="filter-group">
                <label for="status">Estado</label>
                <select id="status" name="status">
                    <option value="">Todos os estados</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pendente</option>
                    <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Em processamento</option>
                    <option value="shipped" <?php echo $status === 'shipped' ? 'selected' : ''; ?>>Enviado</option>
                    <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>Entregue</option>
                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="payment_status">Estado de Pagamento</label>
                <select id="payment_status" name="payment_status">
                    <option value="">Todos os estados</option>
                    <option value="pending" <?php echo $paymentStatus === 'pending' ? 'selected' : ''; ?>>Pendente</option>
                    <option value="paid" <?php echo $paymentStatus === 'paid' ? 'selected' : ''; ?>>Pago</option>
                    <option value="failed" <?php echo $paymentStatus === 'failed' ? 'selected' : ''; ?>>Falhou</option>
                    <option value="refunded" <?php echo $paymentStatus === 'refunded' ? 'selected' : ''; ?>>Reembolsado</option>
                </select>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="<?php echo SITE_URL; ?>/admin/encomendas" class="btn btn-outline">
                    <i class="fas fa-times"></i> Limpar
                </a>
            </div>
        </form>
    </div>
    
    <!-- Orders Table -->
    <div class="table-card">
        <div class="table-header">
            <h2>Encomendas</h2>
            <div class="table-actions">
                <input type="text" class="table-search" placeholder="Pesquisar na tabela...">
            </div>
        </div>
        
        <div class="table-content">
            <?php if (count($orders) > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th data-sort="order_number">Número</th>
                        <th data-sort="customer">Cliente</th>
                        <th data-sort="total">Total</th>
                        <th data-sort="status">Estado</th>
                        <th data-sort="payment_status">Pagamento</th>
                        <th data-sort="date">Data</th>
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
                        <td>
                            <div class="customer-info">
                                <span class="customer-name"><?php echo $order['shipping_name']; ?></span>
                                <span class="customer-email"><?php echo $order['shipping_email']; ?></span>
                            </div>
                        </td>
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
                            <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                                <?php 
                                $paymentStatuses = [
                                    'pending' => 'Pendente',
                                    'paid' => 'Pago',
                                    'failed' => 'Falhou',
                                    'refunded' => 'Reembolsado'
                                ];
                                echo $paymentStatuses[$order['payment_status']] ?? $order['payment_status'];
                                ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?php echo SITE_URL; ?>/admin/encomendas/editar/<?php echo $order['id']; ?>" class="btn btn-sm btn-outline" title="Editar encomenda">
                                    <i class="fas fa-edit"></i>
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
                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>&payment_status=<?php echo $paymentStatus; ?>" class="pagination-prev">
                    <i class="fas fa-chevron-left"></i> Anterior
                </a>
                <?php endif; ?>
                
                <div class="pagination-numbers">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>&payment_status=<?php echo $paymentStatus; ?>" class="pagination-number <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                </div>
                
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>&payment_status=<?php echo $paymentStatus; ?>" class="pagination-next">
                    Seguinte <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="no-results">
                <i class="fas fa-shopping-bag"></i>
                <h3>Nenhuma encomenda encontrada</h3>
                <p>Não foram encontradas encomendas com os filtros selecionados.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Incluir footer do admin
include_once __DIR__ . '/../includes/admin_footer.php';
?>