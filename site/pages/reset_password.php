<?php
/**
 * Redefinir password com token (link do email)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';

$pageTitle = 'Nova password';

if (isLoggedIn()) {
    redirect(SITE_URL . '/conta');
}

$token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
$msg = null;

$resetRow = null;
if ($token !== '') {
    global $pdo;
    $resetRow = dbFetch(
        'SELECT pr.*, u.email FROM password_resets pr INNER JOIN users u ON u.id = pr.user_id WHERE pr.token = ? AND pr.expires_at > NOW()',
        [$token]
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $msg = ['type' => 'error', 'text' => 'Token de segurança inválido.'];
    } elseif (!$resetRow || $token === '') {
        $msg = ['type' => 'error', 'text' => 'Link inválido ou expirado. Pediste um novo em Recuperar password.'];
    } else {
        $p1 = (string) ($_POST['password'] ?? '');
        $p2 = (string) ($_POST['password_confirm'] ?? '');
        if (strlen($p1) < 6) {
            $msg = ['type' => 'error', 'text' => 'A password deve ter pelo menos 6 caracteres.'];
        } elseif ($p1 !== $p2) {
            $msg = ['type' => 'error', 'text' => 'As passwords não coincidem.'];
        } else {
            try {
                global $pdo;
                $hash = password_hash($p1, PASSWORD_DEFAULT);
                $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hash, (int) $resetRow['user_id']]);
                $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([(int) $resetRow['user_id']]);
                $_SESSION['notification'] = ['type' => 'success', 'message' => 'Password atualizada. Já podes iniciar sessão.'];
                redirect(SITE_URL . '/login');
            } catch (Throwable $e) {
                error_log('reset_password: ' . $e->getMessage());
                $msg = ['type' => 'error', 'text' => 'Erro ao guardar. Tenta novamente.'];
            }
        }
    }
    if ($token !== '') {
        $resetRow = dbFetch(
            'SELECT pr.*, u.email FROM password_resets pr INNER JOIN users u ON u.id = pr.user_id WHERE pr.token = ? AND pr.expires_at > NOW()',
            [$token]
        );
    }
}

include_once __DIR__ . '/../includes/header.php';
?>

<section class="breadcrumb">
    <div class="container">
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb-list">
                <li><a href="<?php echo SITE_URL; ?>">Início</a></li>
                <li>Nova password</li>
            </ol>
        </nav>
    </div>
</section>

<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <h1>Nova password</h1>
                <?php if ($resetRow): ?>
                <p>Conta: <strong><?php echo htmlspecialchars($resetRow['email']); ?></strong></p>
                <?php else: ?>
                <p>Link inválido ou expirado.</p>
                <?php endif; ?>
            </div>
            <?php if ($msg): ?>
            <div class="notification <?php echo $msg['type'] === 'success' ? 'success' : 'error'; ?>" style="margin-bottom:1rem;">
                <span><?php echo htmlspecialchars($msg['text']); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($resetRow): ?>
            <form method="post" class="auth-form" action="<?php echo SITE_URL; ?>/redefinir-password">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="form-group">
                    <label for="password">Nova password</label>
                    <input type="password" id="password" name="password" required minlength="6" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="password_confirm">Confirmar password</label>
                    <input type="password" id="password_confirm" name="password_confirm" required minlength="6" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block">Guardar</button>
            </form>
            <?php else: ?>
            <p class="text-center" style="margin:1rem 0;"><a href="<?php echo SITE_URL; ?>/recuperar-password" class="btn btn-outline">Pedir novo link</a></p>
            <?php endif; ?>
            <div class="auth-footer">
                <p><a href="<?php echo SITE_URL; ?>/login">Voltar ao login</a></p>
            </div>
        </div>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
