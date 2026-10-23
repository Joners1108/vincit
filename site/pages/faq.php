<?php
/**
 * Perguntas frequentes (conteúdo alinhado com envios/pagamentos da loja)
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';

$pageTitle = 'Perguntas frequentes';

$shipLabel = formatPrice(vincit_shipping_cost());
$freeLabel = formatPrice(vincit_free_shipping_threshold());

require_once __DIR__ . '/../includes/payment_trust.php';
include_once __DIR__ . '/../includes/header.php';
?>

<section class="breadcrumb">
    <div class="container">
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb-list">
                <li><a href="<?php echo SITE_URL; ?>">Início</a></li>
                <li>FAQ</li>
            </ol>
        </nav>
    </div>
</section>

<section class="support-section">
    <div class="container">
        <div class="support-header">
            <h1>Perguntas frequentes</h1>
            <p>Respostas rápidas sobre compras, envios e pagamentos na Vincit.</p>
        </div>

        <div class="faq-standalone">
            <div class="faq-list">
                <div class="faq-item">
                    <button type="button" class="faq-question" aria-expanded="false">
                        <span>Como compro na loja?</span>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Navegue pela <a href="<?php echo SITE_URL; ?>/loja">loja</a>, escolha o produto, tamanho e cor quando aplicável, e use <strong>Adicionar ao Carrinho</strong>. No carrinho, confirme e siga para o checkout (com sessão iniciada). O pagamento faz-se no ambiente seguro da Stripe.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question" aria-expanded="false">
                        <span>Quais são as formas de pagamento?</span>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Cartão (Visa, Mastercard), <strong>MB Way</strong> e <strong>referência Multibanco</strong>, através da <strong>Stripe</strong>. No checkout será redirecionado para escolher o método.</p>
                        <?php payment_trust_render('faq'); ?>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question" aria-expanded="false">
                        <span>Quanto custa o envio?</span>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Portugal continental: portes <?php echo htmlspecialchars($shipLabel); ?>. Envio <strong>grátis</strong> em encomendas a partir de <?php echo htmlspecialchars($freeLabel); ?>. Para outros destinos, o valor pode variar — em caso de dúvida, <a href="<?php echo SITE_URL; ?>/contacto">contacte-nos</a>.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question" aria-expanded="false">
                        <span>Qual é o prazo de entrega?</span>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Em geral, 3 a 5 dias úteis em Portugal continental após confirmação do pagamento. Receberá atualizações por email; também pode consultar o estado na sua conta.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question" aria-expanded="false">
                        <span>Posso devolver ou trocar?</span>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Sim, dentro do prazo e condições indicados nos <a href="<?php echo SITE_URL; ?>/termos-condicoes">termos</a>. Em caso de dúvida, fale connosco via <a href="<?php echo SITE_URL; ?>/apoio">apoio</a> ou <a href="<?php echo SITE_URL; ?>/contacto">contacto</a>.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question" aria-expanded="false">
                        <span>Como acompanho a minha encomenda?</span>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Após o envio, indicamos o estado na sua <a href="<?php echo SITE_URL; ?>/conta">conta</a> (encomendas). Receberá também notificações por email quando aplicável.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question" aria-expanded="false">
                        <span>O meu tamanho está disponível?</span>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Na página de cada produto vê os tamanhos e stock. Se estiver esgotado, o botão de compra fica indisponível.</p>
                    </div>
                </div>
            </div>

            <div class="faq-contact-cta">
                <h2>Não encontrou resposta?</h2>
                <p>Envie-nos uma mensagem ou use o formulário de apoio.</p>
                <a href="<?php echo SITE_URL; ?>/contacto" class="btn btn-primary">Contacto</a>
                <a href="<?php echo SITE_URL; ?>/apoio" class="btn btn-secondary">Apoio ao cliente</a>
            </div>
        </div>
    </div>
</section>

<?php
include_once __DIR__ . '/../includes/footer.php';
