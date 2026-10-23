<?php
/**
 * Pedido de recuperação de password (estilo RetroVintagePT)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';

$pageTitle = 'Recuperar password';

if (isLoggedIn()) {
    redirect(SITE_URL . '/conta');
}

$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $msg = ['type' => 'error', 'text' => 'Token de segurança inválido.'];
    } else {
        $email = trim((string) ($_POST['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg = ['type' => 'error', 'text' => 'Indica um email válido.'];
        } else {
            $user = dbFetch('SELECT id, name FROM users WHERE email = ? AND status = ?', [$email, 'active']);
            // Mensagem genérica por segurança
            $msg = ['type' => 'success', 'text' => 'Se o email existir na nossa base de dados, receberás instruções em breve.'];
            if ($user) {
                global $pdo;
                try {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+2 hours'));
                    $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([(int) $user['id']]);
                    $pdo->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)')
                        ->execute([(int) $user['id'], $token, $expires]);
                    $link = rtrim(SITE_URL, '/') . '/redefinir-password?token=' . urlencode($token);
                    require_once __DIR__ . '/../includes/mailer.php';
                    vincit_send_password_reset_email($email, (string) $user['name'], $link);
                } catch (Throwable $e) {
                    error_log('forgot_password: ' . $e->getMessage());
                    $msg = ['type' => 'error', 'text' => 'Serviço temporariamente indisponível. Tenta mais tarde.'];
                }
            }
        }
    }
}

include_once __DIR__ . '/../includes/header.php';
?>

<section class="breadcrumb">
    <div class="container">
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb-list">
                <li><a href="<?php echo SITE_URL; ?>">Início</a></li>
                <li>Recuperar password</li>
            </ol>
        </nav>
    </div>
</section>

<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <h1>Recuperar password</h1>
                <p>Indica o teu email e enviamos um link para redefinires a password.</p>
            </div>
            <?php if ($msg): ?>
            <div class="notification <?php echo $msg['type'] === 'success' ? 'success' : 'error'; ?>" style="margin-bottom:1rem;">
                <span><?php echo htmlspecialchars($msg['text']); ?></span>
            </div>
            <?php endif; ?>
            <form method="post" class="auth-form" action="<?php echo SITE_URL; ?>/recuperar-password">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required autocomplete="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block">Enviar link</button>
            </form>
            <div class="auth-footer">
                <p><a href="<?php echo SITE_URL; ?>/login">Voltar ao login</a></p>
            </div>
        </div>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
