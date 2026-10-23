<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';

$pageTitle = 'Pagamento cancelado';
$_SESSION['notification'] = [
    'type' => 'info',
    'message' => 'Pagamento cancelado. A encomenda ficou pendente — podes tentar novamente no checkout.',
];

include_once __DIR__ . '/../includes/header.php';
?>
<section class="breadcrumb"><div class="container"><nav aria-label="Breadcrumb"><ol class="breadcrumb-list"><li><a href="<?php echo SITE_URL; ?>">Início</a></li><li>Pagamento cancelado</li></ol></nav></div></section>
<section class="about-section"><div class="container"><h1>Pagamento não concluído</h1>
<p>Não foi debitado qualquer valor. Podes voltar ao carrinho ou ao checkout.</p>
<p><a class="btn btn-primary" href="<?php echo SITE_URL; ?>/checkout">Tentar novamente</a>
<a class="btn btn-outline" style="margin-left:0.5rem" href="<?php echo SITE_URL; ?>/carrinho">Carrinho</a></p>
</div></section>
<?php include_once __DIR__ . '/../includes/footer.php';
