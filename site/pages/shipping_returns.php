<?php
/**
 * Envio e devoluções (conteúdo alinhado ao RetroVintagePT, valores da loja)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';

$pageTitle = 'Envio e Devoluções';

$free = formatPrice(vincit_free_shipping_threshold());
$ship = formatPrice(vincit_shipping_cost());

include_once __DIR__ . '/../includes/header.php';
?>

<section class="breadcrumb">
    <div class="container">
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb-list">
                <li><a href="<?php echo SITE_URL; ?>">Início</a></li>
                <li>Envio e Devoluções</li>
            </ol>
        </nav>
    </div>
</section>

<main class="policy-page">
    <div class="container policy-container">
        <h1>Envio e Devoluções</h1>
        <p class="policy-updated">Última atualização: <?php echo date('d/m/Y'); ?></p>

        <section class="policy-block">
            <h2>1. Custos de envio</h2>
            <p>Envio gratuito para encomendas iguais ou superiores a <strong><?php echo htmlspecialchars($free); ?></strong> (Portugal continental), conforme configuração atual da loja.</p>
            <ul>
                <li><strong>Portugal continental:</strong> <?php echo htmlspecialchars($ship); ?> (grátis a partir de <?php echo htmlspecialchars($free); ?>)</li>
                <li><strong>Outros destinos:</strong> contacte-nos para orçamento.</li>
            </ul>
        </section>

        <section class="policy-block">
            <h2>2. Prazos de entrega</h2>
            <p><strong>Portugal continental:</strong> em geral 3 a 5 dias úteis após confirmação do pagamento.</p>
            <p>Quando a encomenda for enviada, receberá atualizações por email e pode acompanhar o estado na sua conta, quando disponível.</p>
        </section>

        <section class="policy-block">
            <h2>3. Devoluções</h2>
            <p>Pode devolver os artigos no prazo de <strong>14 dias</strong> após a receção, desde que:</p>
            <ul>
                <li>Os produtos estejam nas condições originais (não usados, com etiquetas).</li>
                <li>Sejam enviados na embalagem original ou equivalente.</li>
            </ul>
            <p>Para iniciar uma devolução, contacte-nos (página de <a href="<?php echo SITE_URL; ?>/contacto">Contacto</a>) com o número da encomenda e o motivo.</p>
            <p>O reembolso será processado após receção e verificação do artigo, pelo mesmo método de pagamento, nos prazos legais.</p>
        </section>

        <section class="policy-block">
            <h2>4. Trocas</h2>
            <p>Para trocar por outro tamanho ou artigo, contacte-nos. A troca está sujeita a disponibilidade de stock.</p>
        </section>

        <p class="policy-contact"><a href="<?php echo SITE_URL; ?>/contacto">Contacte-nos</a> para qualquer dúvida sobre envios ou devoluções.</p>
    </div>
</main>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
