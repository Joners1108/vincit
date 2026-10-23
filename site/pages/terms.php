<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
$pageTitle = 'Termos e Condições';
include_once __DIR__ . '/../includes/header.php';
?>
<section class="breadcrumb"><div class="container"><nav aria-label="Breadcrumb"><ol class="breadcrumb-list"><li><a href="<?php echo SITE_URL; ?>">Início</a></li><li>Termos</li></ol></nav></div></section>
<section class="about-section"><div class="container"><h1>Termos e Condições</h1><p>Condições gerais de utilização da loja. Adapte legalmente antes de produção.</p></div></section>
<?php include_once __DIR__ . '/../includes/footer.php';
