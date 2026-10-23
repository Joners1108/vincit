<?php
http_response_code(404);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
$pageTitle = 'Página não encontrada';
include_once __DIR__ . '/../includes/header.php';
?>
<section class="about-section"><div class="container"><h1>404</h1><p>A página não existe.</p><p><a class="btn btn-primary" href="<?php echo SITE_URL; ?>">Início</a></p></div></section>
<?php include_once __DIR__ . '/../includes/footer.php';
