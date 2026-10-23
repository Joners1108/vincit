<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../includes/stripe_checkout.php';

$pageTitle = 'Pagamento concluído';

$sessionId = isset($_GET['session_id']) ? trim($_GET['session_id']) : '';
if ($sessionId === '') {
    $_SESSION['notification'] = ['type' => 'error', 'message' => 'Sessão de pagamento inválida.'];
    redirect(SITE_URL . '/');
}

$verify = vincit_verify_stripe_checkout_session($sessionId);
if (!$verify['success']) {
    $_SESSION['notification'] = ['type' => 'error', 'message' => 'Não foi possível confirmar o pagamento.'];
    redirect(SITE_URL . '/');
}

$res = vincit_fulfill_stripe_order($verify['session'], null);
if (!$res['ok']) {
    $_SESSION['notification'] = ['type' => 'error', 'message' => $res['error'] ?? 'Erro ao finalizar encomenda'];
    redirect(SITE_URL . '/checkout');
}

$orderNum = $res['order_number'] ?? '';
$_SESSION['notification'] = [
    'type' => 'success',
    'message' => 'Pagamento recebido! Encomenda ' . $orderNum,
];

if (isLoggedIn()) {
    redirect(SITE_URL . '/conta');
}

include_once __DIR__ . '/../includes/header.php';
?>
<section class="breadcrumb"><div class="container"><nav aria-label="Breadcrumb"><ol class="breadcrumb-list"><li><a href="<?php echo SITE_URL; ?>">Início</a></li><li>Obrigado</li></ol></nav></div></section>
<section class="about-section"><div class="container"><h1>Obrigado pela compra</h1>
<p>A sua encomenda <strong><?php echo htmlspecialchars($orderNum); ?></strong> foi registada.</p>
<p><a class="btn btn-primary" href="<?php echo SITE_URL; ?>">Continuar a comprar</a></p>
</div></section>
<?php include_once __DIR__ . '/../includes/footer.php';
