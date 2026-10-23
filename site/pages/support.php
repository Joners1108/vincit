<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Página de Apoio ao Cliente
 */

// Incluir ficheiros de configuração e base de dados
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';

// Definir título da página
$pageTitle = 'Apoio ao Cliente';

// Processar formulário de contacto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Token de segurança inválido'
        ];
        redirect(SITE_URL . '/apoio');
    }
    
    // Obter dados do formulário
    $name = clean($_POST['name'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $subject = clean($_POST['subject'] ?? '');
    $message = clean($_POST['message'] ?? '');
    
    // Validar campos
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Nome é obrigatório';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    }
    
    if (empty($subject)) {
        $errors[] = 'Assunto é obrigatório';
    }
    
    if (empty($message)) {
        $errors[] = 'Mensagem é obrigatória';
    }
    
    // Se houver erros, redirecionar
    if (count($errors) > 0) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => implode('<br>', $errors)
        ];
        redirect(SITE_URL . '/apoio');
    }
    
    require_once __DIR__ . '/../includes/mailer.php';
    vincit_send_support_inquiry_email(vincit_site_email(), $name, $email, $subject, $message);
    
    // Notificação
    $_SESSION['notification'] = [
        'type' => 'success',
        'message' => 'Mensagem enviada com sucesso! Responderemos em breve.'
    ];
    
    redirect(SITE_URL . '/apoio');
}

// Incluir header
include_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<section class="breadcrumb">
    <div class="container">
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb-list">
                <li><a href="<?php echo SITE_URL; ?>">Início</a></li>
                <li>Apoio ao Cliente</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Support Section -->
<section class="support-section">
    <div class="container">
        <div class="support-header">
            <h1>Apoio ao Cliente</h1>
            <p>Estamos aqui para ajudar! Encontre respostas para as suas questões. Consulte também as <a href="<?php echo SITE_URL; ?>/faq">perguntas frequentes</a>.</p>
        </div>
        
        <div class="support-layout">
            <!-- FAQ Section -->
            <div class="faq-section">
                <h2>Perguntas Frequentes</h2>
                
                <div class="faq-list">
                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Como faço um pedido?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Para fazer um pedido, basta navegar pela nossa loja, adicionar produtos ao carrinho e finalizar a compra. O processo é simples e seguro.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Quais são os métodos de pagamento aceites?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Cartão (Visa, Mastercard), MB Way e referência Multibanco, via <strong>Stripe</strong>. No passo de pagamento escolhe o método no ambiente seguro da Stripe.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Qual é o prazo de entrega?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <p>O prazo de entrega varia entre 2 a 5 dias úteis para Portugal continental. Para ilhas e outros países, o prazo pode ser maior.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Como posso devolver um produto?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Pode devolver qualquer produto dentro de 30 dias após a compra. Basta contactar-nos e enviar o produto de volta. O reembolso será processado em 5-7 dias úteis.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Como posso acompanhar a minha encomenda?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Após a confirmação da encomenda, receberá um email com o número de rastreamento. Pode também acompanhar o estado na sua conta.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question">
                            <span>Os produtos têm garantia?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Sim, todos os nossos produtos têm garantia de qualidade. Se encontrar algum defeito, contacte-nos para uma substituição ou reembolso.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="contact-section">
                <h2>Contacte-nos</h2>
                <p>Não encontrou o que procurava? Envie-nos uma mensagem.</p>
                
                <form action="<?php echo SITE_URL; ?>/apoio" method="POST" class="contact-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nome *</label>
                            <input type="text" id="name" name="name" value="<?php echo $_POST['name'] ?? ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" value="<?php echo $_POST['email'] ?? ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="subject">Assunto *</label>
                            <select id="subject" name="subject" required>
                                <option value="">Selecione um assunto</option>
                                <option value="Encomenda" <?php echo ($_POST['subject'] ?? '') === 'Encomenda' ? 'selected' : ''; ?>>Encomenda</option>
                                <option value="Devolução" <?php echo ($_POST['subject'] ?? '') === 'Devolução' ? 'selected' : ''; ?>>Devolução</option>
                                <option value="Produto" <?php echo ($_POST['subject'] ?? '') === 'Produto' ? 'selected' : ''; ?>>Produto</option>
                                <option value="Pagamento" <?php echo ($_POST['subject'] ?? '') === 'Pagamento' ? 'selected' : ''; ?>>Pagamento</option>
                                <option value="Outro" <?php echo ($_POST['subject'] ?? '') === 'Outro' ? 'selected' : ''; ?>>Outro</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="message">Mensagem *</label>
                            <textarea id="message" name="message" rows="5" required><?php echo $_POST['message'] ?? ''; ?></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane"></i> Enviar Mensagem
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Contact Info -->
        <div class="contact-info">
            <div class="info-card">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Morada</h3>
                <p><?php echo htmlspecialchars(vincit_site_address()); ?></p>
            </div>
            
            <div class="info-card">
                <i class="fas fa-phone"></i>
                <h3>Telefone</h3>
                <p><?php echo htmlspecialchars(vincit_site_phone()); ?></p>
            </div>
            
            <div class="info-card">
                <i class="fas fa-envelope"></i>
                <h3>Email</h3>
                <p><?php echo htmlspecialchars(vincit_site_email()); ?></p>
            </div>
            
            <div class="info-card">
                <i class="fas fa-clock"></i>
                <h3>Horário</h3>
                <p>Segunda a Sexta: 9h-18h</p>
            </div>
        </div>
    </div>
</section>

<?php
// Incluir footer
include_once __DIR__ . '/../includes/footer.php';
?>