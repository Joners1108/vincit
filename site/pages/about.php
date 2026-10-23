<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Página Sobre Nós
 */

// Incluir ficheiros de configuração e base de dados
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';

// Definir título da página
$pageTitle = 'Sobre Nós';

// Incluir header
include_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<section class="breadcrumb">
    <div class="container">
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb-list">
                <li><a href="<?php echo SITE_URL; ?>">Início</a></li>
                <li>Sobre Nós</li>
            </ol>
        </nav>
    </div>
</section>

<!-- About Section -->
<section class="about-section">
    <div class="container">
        <div class="about-header">
            <h1>Sobre Nós</h1>
            <p>Conheça a história e valores da Vincit</p>
        </div>
        
        <div class="about-content">
            <div class="about-text">
                <h2>A Nossa História</h2>
                <p>A Vincit surgiu com a ideia de vender roupa com bom preço sem ser lixo — peças que se usam no dia a dia. Estamos no Porto e enviamos para todo o país.</p>
                
                <p>Queremos que qualquer pessoa encontre algo que goste sem pagar uma fortuna. Por isso escolhemos fornecedores com quem podemos contar e tratamos bem quem compra connosco.</p>
                
                <h2>Os Nossos Valores</h2>
                <ul class="values-list">
                    <li><i class="fas fa-check"></i> <strong>Qualidade:</strong> Antes de meter um produto à venda, testamos se o material e o corte fazem sentido.</li>
                    <li><i class="fas fa-check"></i> <strong>Preço:</strong> Tentamos manter margens apertadas para o preço final não disparar.</li>
                    <li><i class="fas fa-check"></i> <strong>Apoio:</strong> Se algo correr mal com a encomenda, respondemos pelo contacto ou apoio no site.</li>
                    <li><i class="fas fa-check"></i> <strong>Stock:</strong> Vamos renovando a loja consoante o que faz sentido vender em cada época.</li>
                </ul>
                
                <h2>Porquê comprar connosco?</h2>
                <p>Processo simples: escolhes na loja, pagas com segurança e recebes em casa. Se não servir, vê as condições de devolução na página de envio.</p>
                
                <p>Oferecemos:</p>
                <ul class="features-list">
                    <li><i class="fas fa-truck"></i> Envio rápido para todo o país</li>
                    <li><i class="fas fa-undo"></i> Devolução fácil em 30 dias</li>
                    <li><i class="fas fa-lock"></i> Pagamento 100% seguro</li>
                    <li><i class="fas fa-headset"></i> Suporte ao cliente dedicado</li>
                    <li><i class="fas fa-gift"></i> Promoções exclusivas</li>
                </ul>
            </div>
            
            <div class="about-image">
                <div class="image-placeholder">
                    <i class="fas fa-store"></i>
                    <span>A Nossa Loja</span>
                </div>
            </div>
        </div>
        
        <div class="team-section">
            <h2>Créditos</h2>
            <p>Site da loja: <strong>Vitor Fontes</strong> (PAP — Prova de Aptidão Profissional).</p>
        </div>
        
        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stat-item">
                <span class="stat-number">10K+</span>
                <span class="stat-label">Clientes Satisfeitos</span>
            </div>
            
            <div class="stat-item">
                <span class="stat-number">5K+</span>
                <span class="stat-label">Produtos Vendidos</span>
            </div>
            
            <div class="stat-item">
                <span class="stat-number">500+</span>
                <span class="stat-label">Produtos em Stock</span>
            </div>
            
            <div class="stat-item">
                <span class="stat-number">99%</span>
                <span class="stat-label">Satisfação</span>
            </div>
        </div>
        
        <!-- CTA Section -->
        <div class="about-cta">
            <h2>Pronto para começar?</h2>
            <p>Descubra as nossas coleções e encontre o seu estilo</p>
            <a href="<?php echo SITE_URL; ?>/loja" class="btn btn-primary btn-lg">Ver Produtos</a>
        </div>
    </div>
</section>

<?php
// Incluir footer
include_once __DIR__ . '/../includes/footer.php';
?>