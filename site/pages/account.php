<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Página de Conta do Utilizador
 */

// Incluir ficheiros de configuração e base de dados
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';

// Definir título da página
$pageTitle = 'A minha conta';

// Verificar se está logado
if (!isLoggedIn()) {
    redirect(SITE_URL . '/login');
}

// Obter utilizador atual
$currentUser = getCurrentUser();

if (!$currentUser) {
    redirect(SITE_URL . '/login');
}

// Obter encomendas do utilizador
$orders = getUserOrders($currentUser['id']);

// Processar atualização de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Validar CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Token de segurança inválido'
        ];
        redirect(SITE_URL . '/conta');
    }
    
    $action = $_POST['action'];
    
    if ($action === 'update_profile') {
        // Atualizar perfil
        $name = clean($_POST['name'] ?? '');
        $phone = clean($_POST['phone'] ?? '');
        $address = clean($_POST['address'] ?? '');
        $city = clean($_POST['city'] ?? '');
        $postalCode = clean($_POST['postal_code'] ?? '');
        $country = clean($_POST['country'] ?? 'Portugal');
        
        // Validar campos
        if (empty($name)) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Nome é obrigatório'
            ];
            redirect(SITE_URL . '/conta');
        }
        
        // Atualizar utilizador
        dbUpdate('users', [
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'postal_code' => $postalCode,
            'country' => $country
        ], 'id = ?', [$currentUser['id']]);
        
        // Atualizar sessão
        $_SESSION['user_name'] = $name;
        
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Perfil atualizado com sucesso!'
        ];
        
        redirect(SITE_URL . '/conta');
        
    } elseif ($action === 'change_password') {
        // Alterar password
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validar campos
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Por favor, preencha todos os campos'
            ];
            redirect(SITE_URL . '/conta');
        }
        
        if ($newPassword !== $confirmPassword) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Novas passwords não coincidem'
            ];
            redirect(SITE_URL . '/conta');
        }
        
        if (strlen($newPassword) < 6) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Password deve ter pelo menos 6 caracteres'
            ];
            redirect(SITE_URL . '/conta');
        }
        
        // Verificar password atual
        if (!password_verify($currentPassword, $currentUser['password'])) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Password atual incorreta'
            ];
            redirect(SITE_URL . '/conta');
        }
        
        // Atualizar password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        dbUpdate('users', [
            'password' => $hashedPassword
        ], 'id = ?', [$currentUser['id']]);
        
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Password alterada com sucesso!'
        ];
        
        redirect(SITE_URL . '/conta');
    }
}

// Pré-visualização de morada (painel)
$addrParts = array_filter([
    trim((string) ($currentUser['address'] ?? '')),
    trim((string) ($currentUser['postal_code'] ?? '')),
    trim((string) ($currentUser['city'] ?? '')),
]);
$addrPreview = implode(', ', $addrParts);
if (strlen($addrPreview) > 90) {
    $addrPreview = substr($addrPreview, 0, 87) . '…';
}
$hasAddress = $addrPreview !== '';

// Incluir header
include_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<section class="breadcrumb">
    <div class="container">
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb-list">
                <li><a href="<?php echo SITE_URL; ?>">Início</a></li>
                <li>A minha conta</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Account Section -->
<section class="account-section account-page">
    <div class="container">
        <header class="account-page-header">
            <h1 class="account-page-title">A minha conta</h1>
            <p class="account-page-lead">Encomendas, favoritos, dados de envio e segurança.</p>
        </header>

        <div class="account-layout">
            <!-- Account Sidebar -->
            <aside class="account-sidebar">
                <div class="account-user">
                    <div class="user-avatar" aria-hidden="true">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-info">
                        <h3 class="user-info__name"><?php echo htmlspecialchars($currentUser['name']); ?></h3>
                        <p class="user-info__email"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                    </div>
                </div>
                
                <nav class="account-nav">
                    <ul>
                        <li><a href="#dashboard" class="active" data-tab="dashboard"><i class="fas fa-tachometer-alt"></i> Painel</a></li>
                        <li><a href="#orders" data-tab="orders"><i class="fas fa-shopping-bag"></i> Encomendas</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/favoritos"><i class="fas fa-heart"></i> Favoritos</a></li>
                        <li><a href="#profile" data-tab="profile"><i class="fas fa-user-edit"></i> Editar Perfil</a></li>
                        <li><a href="#password" data-tab="password"><i class="fas fa-lock"></i> Alterar Password</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/logout"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                    </ul>
                </nav>
            </aside>
            
            <!-- Account Content -->
            <div class="account-content">
                <!-- Dashboard Tab -->
                <div class="account-tab active" id="dashboard">
                    <h2 class="account-tab__title">Painel</h2>
                    <p class="account-tab__intro">Olá, <strong><?php echo htmlspecialchars($currentUser['name']); ?></strong>. Aqui tens um resumo da tua conta.</p>
                    
                    <div class="dashboard-stats">
                        <a href="#orders" class="stat-card stat-card--link" data-tab="orders">
                            <i class="fas fa-shopping-bag" aria-hidden="true"></i>
                            <div class="stat-info">
                                <span class="stat-number"><?php echo count($orders); ?></span>
                                <span class="stat-label">Encomendas</span>
                            </div>
                            <span class="stat-card__hint">Ver histórico <i class="fas fa-chevron-right" aria-hidden="true"></i></span>
                        </a>
                        <div class="stat-card stat-card--address">
                            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                            <div class="stat-info">
                                <?php if ($hasAddress): ?>
                                <span class="stat-address"><?php echo htmlspecialchars($addrPreview); ?></span>
                                <span class="stat-label">Morada de envio</span>
                                <?php else: ?>
                                <span class="stat-label stat-label--strong">Morada em falta</span>
                                <span class="stat-label">Adiciona morada para checkout mais rápido.</span>
                                <?php endif; ?>
                            </div>
                            <a href="#profile" class="stat-card__action" data-tab="profile"><?php echo $hasAddress ? 'Editar' : 'Preencher'; ?></a>
                        </div>
                    </div>
                    
                    <div class="recent-orders">
                        <div class="recent-orders__head">
                            <h3>Encomendas recentes</h3>
                            <?php if (count($orders) > 0): ?>
                            <a href="#orders" class="recent-orders__all" data-tab="orders">Ver todas</a>
                            <?php endif; ?>
                        </div>
                        <?php if (count($orders) > 0): ?>
                        <div class="orders-table-wrap">
                        <div class="orders-table">
                            <table>
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
                                    <?php foreach (array_slice($orders, 0, 5) as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['order_number']); ?></td>
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
                                            <a href="<?php echo SITE_URL; ?>/conta/encomenda/<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">Ver</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        </div>
                        <?php else: ?>
                        <div class="account-empty">
                            <p>Ainda não tens encomendas.</p>
                            <a href="<?php echo SITE_URL; ?>/loja" class="btn btn-primary">Ir à loja</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Orders Tab -->
                <div class="account-tab" id="orders">
                    <h2 class="account-tab__title">As minhas encomendas</h2>
                    <p class="account-tab__intro">Histórico e estado de cada pedido.</p>
                    
                    <?php if (count($orders) > 0): ?>
                    <div class="orders-list">
                        <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <span class="order-number">Encomenda #<?php echo htmlspecialchars($order['order_number']); ?></span>
                                    <span class="order-date"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="order-status">
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
                                </div>
                            </div>
                            
                            <div class="order-details">
                                <div class="order-total">
                                    <span>Total:</span>
                                    <span class="total-amount"><?php echo formatPrice($order['total']); ?></span>
                                </div>
                                
                                <div class="order-payment">
                                    <span>Pagamento:</span>
                                    <span class="payment-status <?php echo $order['payment_status']; ?>">
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
                                </div>
                            </div>
                            
                            <div class="order-actions">
                                <a href="<?php echo SITE_URL; ?>/conta/encomenda/<?php echo $order['id']; ?>" class="btn btn-outline">Ver Detalhes</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="account-empty">
                        <p>Ainda não tens encomendas.</p>
                        <a href="<?php echo SITE_URL; ?>/loja" class="btn btn-primary">Explorar loja</a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Profile Tab -->
                <div class="account-tab" id="profile">
                    <h2 class="account-tab__title">Dados e morada</h2>
                    <p class="account-tab__intro">Estes dados podem ser usados no envio das tuas encomendas.</p>
                    
                    <form action="<?php echo SITE_URL; ?>/conta" method="POST" class="profile-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Nome Completo *</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($currentUser['name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" value="<?php echo $currentUser['email']; ?>" disabled>
                                <small>O email não pode ser alterado</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Telefone</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="address">Morada</label>
                                <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($currentUser['address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">Cidade</label>
                                <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($currentUser['city'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="postal_code">Código Postal</label>
                                <input type="text" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($currentUser['postal_code'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="country">País</label>
                                <select id="country" name="country">
                                    <option value="Portugal" <?php echo $currentUser['country'] === 'Portugal' ? 'selected' : ''; ?>>Portugal</option>
                                    <option value="Espanha" <?php echo $currentUser['country'] === 'Espanha' ? 'selected' : ''; ?>>Espanha</option>
                                    <option value="França" <?php echo $currentUser['country'] === 'França' ? 'selected' : ''; ?>>França</option>
                                    <option value="Alemanha" <?php echo $currentUser['country'] === 'Alemanha' ? 'selected' : ''; ?>>Alemanha</option>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Guardar Alterações</button>
                    </form>
                </div>
                
                <!-- Password Tab -->
                <div class="account-tab" id="password">
                    <h2 class="account-tab__title">Alterar password</h2>
                    <p class="account-tab__intro">Escolhe uma password forte e única para esta conta.</p>
                    
                    <form action="<?php echo SITE_URL; ?>/conta" method="POST" class="password-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="current_password">Password Atual *</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password">Nova Password *</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirmar Nova Password *</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Alterar Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Incluir footer
include_once __DIR__ . '/../includes/footer.php';
?>