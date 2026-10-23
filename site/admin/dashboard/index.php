<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Dashboard de Administração
 */

// Incluir ficheiros de configuração e base de dados
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';

// Verificar se está logado e é admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login');
}

require_once __DIR__ . '/../../includes/admin_notifications_lib.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_notifications_read']) && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    vincit_admin_notify_mark_all_read();
    $_SESSION['notification'] = [
        'type' => 'success',
        'message' => 'Notificações marcadas como lidas.',
    ];
    redirect(SITE_URL . '/admin');
}

// Definir título da página
$pageTitle = 'Dashboard';

$adminNotifications = vincit_admin_notify_recent(20);

// Obter estatísticas
$stats = getDashboardStats();

// Obter encomendas recentes
$recentOrders = dbFetchAll("SELECT o.*, u.name as user_name 
                           FROM orders o 
                           LEFT JOIN users u ON o.user_id = u.id 
                           ORDER BY o.created_at DESC 
                           LIMIT 10");

// Obter produtos com stock baixo
$lowStockProducts = dbFetchAll("SELECT * FROM products 
                               WHERE stock <= 5 AND status = 'active' 
                               ORDER BY stock ASC 
                               LIMIT 10");

// Obter utilizadores recentes
$recentUsers = dbFetchAll("SELECT * FROM users 
                          WHERE role = 'customer' 
                          ORDER BY created_at DESC 
                          LIMIT 10");

// Incluir header do admin
include_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Dashboard Content -->
<div class="admin-content">
    <div class="admin-header">
        <h1>Dashboard</h1>
        <p>Bem-vindo ao painel de administração</p>
    </div>
    
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number"><?php echo $stats['total_products']; ?></span>
                <span class="stat-label">Produtos</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number"><?php echo $stats['total_orders']; ?></span>
                <span class="stat-label">Encomendas</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number"><?php echo $stats['total_users']; ?></span>
                <span class="stat-label">Utilizadores</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-euro-sign"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number"><?php echo formatPrice($stats['total_revenue']); ?></span>
                <span class="stat-label">Receita Total</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number"><?php echo $stats['pending_orders']; ?></span>
                <span class="stat-label">Encomendas Pendentes</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number"><?php echo formatPrice($stats['monthly_revenue']); ?></span>
                <span class="stat-label">Receita do Mês</span>
            </div>
        </div>
    </div>
    
    <!-- Dashboard Grid -->
    <div class="dashboard-grid">
        <div class="dashboard-card" id="notificacoes">
            <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;">
                <h2>Notificações</h2>
                <?php if (count($adminNotifications) > 0 && vincit_admin_notify_unread_count() > 0): ?>
                <form method="post" style="margin:0;">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="mark_notifications_read" value="1">
                    <button type="submit" class="btn btn-sm btn-outline">Marcar todas como lidas</button>
                </form>
                <?php endif; ?>
            </div>
            <div class="card-content">
                <?php if (count($adminNotifications) > 0): ?>
                <ul class="admin-notify-list" style="list-style:none;padding:0;margin:0;">
                    <?php foreach ($adminNotifications as $n): ?>
                    <li style="padding:0.75rem 0;border-bottom:1px solid var(--a-border);<?php echo empty($n['is_read']) ? 'background:rgba(201,162,39,0.08);margin:0 -1rem;padding-left:1rem;padding-right:1rem;' : ''; ?>">
                        <div style="font-weight:600;"><?php echo htmlspecialchars($n['title']); ?></div>
                        <?php if (!empty($n['body'])): ?>
                        <div style="font-size:0.9rem;opacity:0.9;margin-top:0.25rem;"><?php echo nl2br(htmlspecialchars($n['body'])); ?></div>
                        <?php endif; ?>
                        <div style="font-size:0.8rem;opacity:0.75;margin-top:0.35rem;">
                            <?php echo date('d/m/Y H:i', strtotime($n['created_at'])); ?>
                            <?php if (!empty($n['link'])): ?>
                            — <a href="<?php echo htmlspecialchars($n['link']); ?>">Abrir</a>
                            <?php endif; ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p>Sem notificações.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2>Encomendas Recentes</h2>
                <a href="<?php echo SITE_URL; ?>/admin/encomendas" class="btn btn-sm btn-outline">Ver Todas</a>
            </div>
            <div class="card-content">
                <?php if (count($recentOrders) > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td><a href="<?php echo SITE_URL; ?>/admin/encomendas/editar/<?php echo $order['id']; ?>"><?php echo $order['order_number']; ?></a></td>
                            <td><?php echo $order['user_name'] ?? 'Guest'; ?></td>
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
                            <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>Nenhuma encomenda encontrada.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Low Stock Products -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2>Stock Baixo</h2>
                <a href="<?php echo SITE_URL; ?>/admin/produtos" class="btn btn-sm btn-outline">Ver Todos</a>
            </div>
            <div class="card-content">
                <?php if (count($lowStockProducts) > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Stock</th>
                            <th>Preço</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lowStockProducts as $product): ?>
                        <tr>
                            <td><a href="<?php echo SITE_URL; ?>/admin/produtos/editar/<?php echo $product['id']; ?>"><?php echo $product['name']; ?></a></td>
                            <td>
                                <span class="stock-badge <?php echo $product['stock'] <= 0 ? 'out-of-stock' : 'low-stock'; ?>">
                                    <?php echo $product['stock']; ?>
                                </span>
                            </td>
                            <td><?php echo formatPrice($product['price']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>Todos os produtos têm stock suficiente.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Users -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2>Utilizadores Recentes</h2>
                <a href="<?php echo SITE_URL; ?>/admin/utilizadores" class="btn btn-sm btn-outline">Ver Todos</a>
            </div>
            <div class="card-content">
                <?php if (count($recentUsers) > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Data de Registo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                        <tr>
                            <td><a href="<?php echo SITE_URL; ?>/admin/utilizadores/<?php echo $user['id']; ?>"><?php echo $user['name']; ?></a></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>Nenhum utilizador registado.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2>Ações Rápidas</h2>
            </div>
            <div class="card-content">
                <div class="quick-actions">
                    <a href="<?php echo SITE_URL; ?>/admin/produtos/novo" class="quick-action">
                        <i class="fas fa-plus"></i>
                        <span>Adicionar Produto</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/categorias/nova" class="quick-action">
                        <i class="fas fa-folder-plus"></i>
                        <span>Adicionar Categoria</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/encomendas" class="quick-action">
                        <i class="fas fa-eye"></i>
                        <span>Ver Encomendas</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>" target="_blank" class="quick-action">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Ver Loja</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir footer do admin
include_once __DIR__ . '/../includes/admin_footer.php';
?>