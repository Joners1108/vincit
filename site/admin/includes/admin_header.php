<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Header do Painel de Administração
 */

$currentUser = getCurrentUser();
require_once __DIR__ . '/../../includes/admin_notifications_lib.php';
$vincitAdminNotifyUnread = vincit_admin_notify_unread_count();

$vincitAdminPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$vincitAdminPath = rtrim(str_replace('\\', '/', $vincitAdminPath), '/') ?: '/';
$vincitIsDashboard = (bool) preg_match('#/admin$#', $vincitAdminPath) || (bool) preg_match('#/admin/dashboard$#', $vincitAdminPath);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Admin' : 'Admin'; ?> - <?php echo SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>/assets/images/favicon.ico">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Admin -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css?v=<?php echo @filemtime(__DIR__ . '/../../assets/css/admin.css') ?: '1'; ?>">
</head>
<body class="admin-body">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <a href="<?php echo SITE_URL; ?>/admin" class="sidebar-logo">
                <h1>VINCIT</h1>
                <span>Admin</span>
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>/admin" class="nav-link <?php echo $vincitIsDashboard ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>/admin/produtos" class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'produtos') !== false ? 'active' : ''; ?>">
                        <i class="fas fa-box"></i>
                        <span>Produtos</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>/admin/categorias" class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'categorias') !== false ? 'active' : ''; ?>">
                        <i class="fas fa-folder"></i>
                        <span>Categorias</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>/admin/encomendas" class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'encomendas') !== false ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Encomendas</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>/admin/utilizadores" class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'utilizadores') !== false ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span>Utilizadores</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="<?php echo rtrim(SITE_URL, '/'); ?>/admin/configuracoes/" class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'configuracoes') !== false ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i>
                        <span>Configurações</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="sidebar-footer">
            <a href="<?php echo SITE_URL; ?>" target="_blank" class="sidebar-link">
                <i class="fas fa-external-link-alt"></i>
                <span>Ver Loja</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/logout" class="sidebar-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sair</span>
            </a>
        </div>
    </aside>
    
    <!-- Admin Main -->
    <div class="admin-main">
        <!-- Admin Top Bar -->
        <header class="admin-topbar">
            <button class="sidebar-toggle" aria-label="Toggle Sidebar">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="topbar-search">
                <form action="<?php echo SITE_URL; ?>/admin/pesquisa" method="GET">
                    <input type="text" name="q" placeholder="Pesquisar...">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            
            <div class="topbar-actions">
                <div class="topbar-notifications">
                    <a href="<?php echo SITE_URL; ?>/admin#notificacoes" class="notification-btn" title="Notificações">
                        <i class="fas fa-bell"></i>
                        <?php if ($vincitAdminNotifyUnread > 0): ?>
                        <span class="notification-badge"><?php echo (int) $vincitAdminNotifyUnread; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
                
                <div class="topbar-user">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-info">
                        <span class="user-name"><?php echo $currentUser['name']; ?></span>
                        <span class="user-role">Administrador</span>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Admin Content Container -->
        <div class="admin-container">