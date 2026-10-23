<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Página de Registo (nome, email, password)
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../includes/mailer.php';

$pageTitle = 'Criar Conta';

if (isLoggedIn()) {
    redirect(SITE_URL . '/conta');
}

$safeRedirect = static function (): string {
    $default = SITE_URL . '/conta';
    if (!empty($_GET['redirect'])) {
        $r = (string) $_GET['redirect'];
        if (strpos($r, SITE_URL) === 0) {
            return $r;
        }
    }
    return $default;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Token de segurança inválido',
        ];
        redirect(SITE_URL . '/registo');
    }

    $name = clean($_POST['name'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $terms = isset($_POST['terms']);

    $errors = [];

    if ($name === '') {
        $errors[] = 'Nome é obrigatório';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    }

    if ($password === '') {
        $errors[] = 'Password é obrigatória';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password deve ter pelo menos 6 caracteres';
    }

    if ($password !== $passwordConfirm) {
        $errors[] = 'Passwords não coincidem';
    }

    if (!$terms) {
        $errors[] = 'Deve aceitar os termos e condições';
    }

    if (dbExists('users', 'email = ?', [$email])) {
        $errors[] = 'Este email já está registado';
    }

    if (count($errors) > 0) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => implode('<br>', $errors),
        ];
        redirect(SITE_URL . '/registo' . (!empty($_GET['redirect']) ? '?redirect=' . urlencode((string) $_GET['redirect']) : ''));
    }

    try {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $userId = dbInsert('users', [
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
            'phone' => null,
            'address' => null,
            'city' => null,
            'postal_code' => null,
            'country' => 'Portugal',
            'role' => 'customer',
            'status' => 'active',
        ]);

        if (!$userId) {
            throw new Exception('Erro ao criar conta');
        }

        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'customer';

        mergeGuestCartIntoUser((int) $userId);

        vincit_send_welcome_email($email, $name);

        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Conta criada com sucesso! Bem-vindo, ' . $name,
        ];

        redirect($safeRedirect());
    } catch (Exception $e) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Erro ao criar conta: ' . $e->getMessage(),
        ];
        redirect(SITE_URL . '/registo');
    }
}

include_once __DIR__ . '/../includes/header.php';

$regRedirectQ = !empty($_GET['redirect']) ? '?redirect=' . urlencode((string) $_GET['redirect']) : '';
$formAction = SITE_URL . '/registo' . $regRedirectQ;
?>

<section class="breadcrumb">
    <div class="container">
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb-list">
                <li><a href="<?php echo SITE_URL; ?>">Início</a></li>
                <li>Criar Conta</li>
            </ol>
        </nav>
    </div>
</section>

<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <h1>Criar Conta</h1>
                <p>Regista-te com nome, email e password. Podes completar morada de envio no perfil ou no checkout.</p>
            </div>

            <form action="<?php echo htmlspecialchars($formAction); ?>" method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="form-group">
                    <label for="name">Nome *</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required autocomplete="name">
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autocomplete="email">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required autocomplete="new-password" minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="password_confirm">Confirmar password *</label>
                        <input type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password" minlength="6">
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="terms" required>
                        <span>Aceito os <a href="<?php echo SITE_URL; ?>/termos-condicoes" target="_blank" rel="noopener">Termos e Condições</a> e a <a href="<?php echo SITE_URL; ?>/politica-privacidade" target="_blank" rel="noopener">Política de Privacidade</a></span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">Criar Conta</button>
            </form>

            <div class="auth-footer">
                <p>Já tens conta? <a href="<?php echo SITE_URL; ?>/login<?php echo $regRedirectQ ? htmlspecialchars($regRedirectQ) : ''; ?>">Iniciar sessão</a></p>
            </div>
        </div>
    </div>
</section>

<?php
include_once __DIR__ . '/../includes/footer.php';
