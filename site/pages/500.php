<?php
http_response_code(500);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
$pageTitle = 'Erro';
include_once __DIR__ . '/../includes/header.php';
?>
<section class="about-section"><div class="container"><h1>Erro no servidor</h1><p>Tente novamente mais tarde.</p><p><a class="btn btn-primary" href="<?php echo SITE_URL; ?>">Início</a></p></div></section>
<?php include_once __DIR__ . '/../includes/footer.php';
