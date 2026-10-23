<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Header do Site
 */

// Obter contagem do carrinho
$cartCount = getCartCount();

// Obter utilizador atual
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo META_DESCRIPTION; ?>">
    <meta name="keywords" content="<?php echo META_KEYWORDS; ?>">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>/assets/images/favicon.ico">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Principal -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/style.css'); ?>">
    <!-- Tema alinhado ao RetroVintagePT (tipografia + cartões + hero) -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/retro-theme.css?v=<?php echo @filemtime(__DIR__ . '/../assets/css/retro-theme.css') ?: '1'; ?>">
    
    <!-- CSS Adicional -->
    <?php if (isset($additionalCSS)): ?>
    <?php foreach ($additionalCSS as $css): ?>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/<?php echo $css; ?>">
    <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Header — faixas a 100% da largura; conteúdo limitado pelo .container -->
    <header class="main-header">
        <div class="header-top-bar">
            <div class="container">
                <div class="header-top">
                    <div class="header-contact">
                        <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars(vincit_site_phone()); ?></span>
                        <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars(vincit_site_email()); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="header-main-bar">
            <div class="container">
                <div class="header-main">
                    <div class="logo">
                        <a href="<?php echo SITE_URL; ?>">
                            <h1>VINCIT</h1>
                        </a>
                    </div>

                    <div class="search-bar">
                        <form action="<?php echo SITE_URL; ?>/loja" method="GET">
                            <input type="text" name="search" placeholder="Pesquisar produtos..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>

                    <div class="header-actions">
                        <div class="user-menu">
                            <?php if ($currentUser): ?>
                            <a href="<?php echo SITE_URL; ?>/conta" class="user-link">
                                <i class="fas fa-user"></i>
                                <span><?php echo $currentUser['name']; ?></span>
                            </a>
                            <a href="<?php echo SITE_URL; ?>/favoritos" class="favorites-link" title="Favoritos">
                                <i class="fas fa-heart" aria-hidden="true"></i>
                                <span class="sr-only">Favoritos</span>
                            </a>
                            <?php if (isAdmin()): ?>
                            <a href="<?php echo SITE_URL; ?>/admin" class="admin-link">
                                <i class="fas fa-cog"></i>
                            </a>
                            <?php endif; ?>
                            <a href="<?php echo SITE_URL; ?>/logout" class="logout-link">
                                <i class="fas fa-sign-out-alt"></i>
                            </a>
                            <?php else: ?>
                            <a href="<?php echo SITE_URL; ?>/login" class="user-link">
                                <i class="fas fa-user"></i>
                                <span>Entrar</span>
                            </a>
                            <?php endif; ?>
                        </div>

                         <div class="cart-icon">
                             <a href="<?php echo SITE_URL; ?>/carrinho" id="cart-toggle">
                                 <i class="fas fa-shopping-cart"></i>
                                 <?php if ($cartCount > 0): ?>
                                 <span class="cart-count"><?php echo $cartCount; ?></span>
                                 <?php endif; ?>
                             </a>
                             <div class="mini-cart" id="mini-cart">
                                 <div class="mini-cart-header">
                                     <h4>O teu carrinho</h4>
                                     <button class="mini-cart-close" id="mini-cart-close"><i class="fas fa-times"></i></button>
                                 </div>
                                 <div class="mini-cart-body" id="mini-cart-body">
                                     <!-- Carrinho será carregado aqui -->
                                     <div class="mini-cart-empty">
                                         <p>O teu carrinho está vazio</p>
                                     </div>
                                 </div>
                                 <div class="mini-cart-footer" id="mini-cart-footer">
                                     <a href="<?php echo SITE_URL; ?>/carrinho" class="btn btn-primary btn-sm btn-block mini-cart-footer__cta">Ver Carrinho</a>
                                 </div>
                             </div>
                         </div>

                        <button class="mobile-menu-toggle" aria-label="Menu">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="header-nav-bar">
            <div class="container">
                <nav class="main-nav">
                    <ul class="nav-list">
                        <li><a href="<?php echo SITE_URL; ?>">Início</a></li>
                        <li class="has-dropdown">
                            <a href="<?php echo SITE_URL; ?>/loja">Loja</a>
                            <ul class="dropdown">
                                <li><a href="<?php echo SITE_URL; ?>/loja/homem">Homem</a></li>
                                <li><a href="<?php echo SITE_URL; ?>/loja/mulher">Mulher</a></li>
                            </ul>
                        </li>
                        <li><a href="<?php echo SITE_URL; ?>/sobre">Sobre</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/apoio">Apoio</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/faq">FAQ</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/envio-devolucoes">Envios</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/guia-tamanhos">Tamanhos</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/contacto">Contacto</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    
    <!-- Mobile Menu -->
    <div class="mobile-menu">
        <div class="mobile-menu-header">
            <div class="logo">
                <a href="<?php echo SITE_URL; ?>">
                    <h1>VINCIT</h1>
                </a>
            </div>
            <button class="mobile-menu-close" aria-label="Fechar Menu">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <nav class="mobile-nav">
            <ul class="mobile-nav-list">
                <li><a href="<?php echo SITE_URL; ?>">Início</a></li>
                <li><a href="<?php echo SITE_URL; ?>/loja">Loja</a></li>
                <li><a href="<?php echo SITE_URL; ?>/loja/homem">Homem</a></li>
                <li><a href="<?php echo SITE_URL; ?>/loja/mulher">Mulher</a></li>
                <li><a href="<?php echo SITE_URL; ?>/sobre">Sobre</a></li>
                <li><a href="<?php echo SITE_URL; ?>/apoio">Apoio</a></li>
                <li><a href="<?php echo SITE_URL; ?>/faq">FAQ</a></li>
                <li><a href="<?php echo SITE_URL; ?>/envio-devolucoes">Envio e devoluções</a></li>
                <li><a href="<?php echo SITE_URL; ?>/guia-tamanhos">Guia de tamanhos</a></li>
                <li><a href="<?php echo SITE_URL; ?>/contacto">Contacto</a></li>
                <?php if ($currentUser): ?>
                <li><a href="<?php echo SITE_URL; ?>/conta">Minha Conta</a></li>
                <?php if (isAdmin()): ?>
                <li><a href="<?php echo SITE_URL; ?>/admin">Administração</a></li>
                <?php endif; ?>
                <li><a href="<?php echo SITE_URL; ?>/logout">Sair</a></li>
                <?php else: ?>
                <li><a href="<?php echo SITE_URL; ?>/login">Entrar</a></li>
                <li><a href="<?php echo SITE_URL; ?>/registo">Registar</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    
    <!-- Main Content -->
    <main class="main-content">