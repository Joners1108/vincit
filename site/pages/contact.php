<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
$pageTitle = 'Contacto';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['notification'] = ['type' => 'error', 'message' => 'Token inválido'];
        redirect(SITE_URL . '/contacto');
    }
    $_SESSION['notification'] = ['type' => 'success', 'message' => 'Mensagem recebida. Entraremos em contacto em breve.'];
    redirect(SITE_URL . '/contacto');
}
include_once __DIR__ . '/../includes/header.php';
?>
<section class="breadcrumb"><div class="container"><nav aria-label="Breadcrumb"><ol class="breadcrumb-list"><li><a href="<?php echo SITE_URL; ?>">Início</a></li><li>Contacto</li></ol></nav></div></section>
<section class="about-section"><div class="container"><h1>Contacto</h1>
<form method="post" class="checkout-form" style="max-width:560px;margin-top:2rem;">
<input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
<div class="form-group"><label for="nome">Nome</label><input type="text" id="nome" name="nome" required></div>
<div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email" required></div>
<div class="form-group"><label for="mensagem">Mensagem</label><textarea id="mensagem" name="mensagem" rows="5" required></textarea></div>
<button type="submit" class="btn btn-primary">Enviar</button>
</form></div></section>
<?php include_once __DIR__ . '/../includes/footer.php';
