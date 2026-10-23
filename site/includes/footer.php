<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Footer do Site (estrutura próxima do RetroVintagePT)
 */
?>
    </main>
    
    <footer class="main-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3><i class="fas fa-store" aria-hidden="true"></i> <?php echo htmlspecialchars(SITE_NAME); ?></h3>
                    <p>Streetwear e cultura urbana — loja online com envio em Portugal. Pagamentos seguros com Stripe.</p>
                </div>

                <div class="footer-section">
                    <h3><i class="fas fa-link" aria-hidden="true"></i> Loja</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo SITE_URL; ?>"><i class="fas fa-home" aria-hidden="true"></i> Início</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/loja"><i class="fas fa-th" aria-hidden="true"></i> Todos os produtos</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/loja/homem"><i class="fas fa-mars" aria-hidden="true"></i> Homem</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/loja/mulher"><i class="fas fa-venus" aria-hidden="true"></i> Mulher</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/carrinho"><i class="fas fa-shopping-bag" aria-hidden="true"></i> Carrinho</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3><i class="fas fa-user" aria-hidden="true"></i> Cliente</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo SITE_URL; ?>/conta"><i class="fas fa-id-card" aria-hidden="true"></i> A minha conta</a></li>
                        <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                        <li><a href="<?php echo SITE_URL; ?>/favoritos"><i class="fas fa-heart" aria-hidden="true"></i> Favoritos</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo SITE_URL; ?>/login"><i class="fas fa-sign-in-alt" aria-hidden="true"></i> Entrar</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/registo"><i class="fas fa-user-plus" aria-hidden="true"></i> Registo</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/faq"><i class="fas fa-question-circle" aria-hidden="true"></i> FAQ</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/envio-devolucoes"><i class="fas fa-truck" aria-hidden="true"></i> Envio e Devoluções</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/guia-tamanhos"><i class="fas fa-ruler-combined" aria-hidden="true"></i> Guia de Tamanhos</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3><i class="fas fa-headset" aria-hidden="true"></i> Suporte</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo SITE_URL; ?>/apoio"><i class="fas fa-life-ring" aria-hidden="true"></i> Apoio ao Cliente</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/contacto"><i class="fas fa-envelope" aria-hidden="true"></i> Contacto</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/sobre"><i class="fas fa-info-circle" aria-hidden="true"></i> Sobre Nós</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/politica-privacidade"><i class="fas fa-shield-alt" aria-hidden="true"></i> Privacidade</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/termos-condicoes"><i class="fas fa-file-contract" aria-hidden="true"></i> Termos</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Contacto</h3>
                    <ul class="footer-contact">
                        <li>
                            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                            <span><?php echo htmlspecialchars(vincit_site_address()); ?></span>
                        </li>
                        <li>
                            <i class="fas fa-phone" aria-hidden="true"></i>
                            <span><?php echo htmlspecialchars(vincit_site_phone()); ?></span>
                        </li>
                        <li>
                            <i class="fas fa-envelope" aria-hidden="true"></i>
                            <span><?php echo htmlspecialchars(vincit_site_email()); ?></span>
                        </li>
                        <li>
                            <i class="fas fa-clock" aria-hidden="true"></i>
                            <span>Seg–Sex: 9h–18h</span>
                        </li>
                    </ul>
                </div>
            </div>

            <?php require_once __DIR__ . '/payment_trust.php'; payment_trust_render('footer'); ?>
            
            <div class="footer-bottom">
                <div class="footer-legal">
                    <a href="<?php echo SITE_URL; ?>/politica-privacidade">Política de Privacidade</a>
                    <a href="<?php echo SITE_URL; ?>/termos-condicoes">Termos e Condições</a>
                    <button type="button" class="footer-cookie-settings" id="cookie-preferences-open">Definições de cookies</button>
                </div>
                <div class="footer-copyright">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Todos os direitos reservados.</p>
                </div>
                <div class="footer-payments">
                    <span>Stripe</span>
                    <i class="fab fa-cc-stripe" aria-hidden="true"></i>
                </div>
            </div>
        </div>
    </footer>

    <div id="cookie-banner" class="cookie-banner" role="dialog" aria-modal="false" aria-labelledby="cookie-banner-title" aria-describedby="cookie-banner-desc" aria-hidden="true" hidden>
        <div class="cookie-banner__inner">
            <div class="cookie-banner__text">
                <h2 id="cookie-banner-title" class="cookie-banner__title">Valorizamos a tua privacidade</h2>
                <p id="cookie-banner-desc" class="cookie-banner__desc">
                    Utilizamos <strong>cookies essenciais</strong> para o funcionamento do site (sessão, carrinho de compras e segurança).
                    Com o teu consentimento, podemos usar cookies adicionais para melhorar a experiência e estatísticas.
                    Saber mais na <a href="<?php echo SITE_URL; ?>/politica-privacidade">Política de Privacidade</a>.
                </p>
            </div>
            <div class="cookie-banner__actions">
                <button type="button" class="btn btn-outline cookie-banner__btn" id="cookie-btn-essential">Apenas essenciais</button>
                <button type="button" class="btn btn-primary cookie-banner__btn" id="cookie-btn-accept">Aceitar todos</button>
            </div>
        </div>
    </div>
    
    <script>
    window.VINCIT_SITE_URL = <?php echo json_encode(SITE_URL, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    window.VINCIT_PRODUCT_IMG_BASE = <?php echo json_encode(rtrim(SITE_URL, '/') . '/' . VINCIT_PRODUCT_IMAGES_PATH, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    </script>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js?v=12"></script>
    
    <?php if (isset($additionalJS)): ?>
    <?php foreach ($additionalJS as $js): ?>
    <script src="<?php echo SITE_URL; ?>/assets/js/<?php echo $js; ?>"></script>
    <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['notification'])): ?>
    <div class="notification <?php echo $_SESSION['notification']['type']; ?>">
        <span><?php echo $_SESSION['notification']['message']; ?></span>
        <button type="button" class="notification-close"><i class="fas fa-times"></i></button>
    </div>
    <?php unset($_SESSION['notification']); ?>
    <?php endif; ?>
</body>
</html>
