<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Página de Login
 */

// Incluir ficheiros de configuração e base de dados
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';

// Definir título da página
$pageTitle = 'Iniciar Sessão';

$loginRedirectQ = !empty($_GET['redirect']) ? '?redirect=' . urlencode((string) $_GET['redirect']) : '';

// Verificar se já está logado
if (isLoggedIn()) {
    redirect(SITE_URL . '/conta');
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Token de segurança inválido'
        ];
        redirect(SITE_URL . '/login' . $loginRedirectQ);
    }
    
    // Obter dados do formulário
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validar campos
    if (empty($email) || empty($password)) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Por favor, preencha todos os campos'
        ];
        redirect(SITE_URL . '/login' . $loginRedirectQ);
    }
    
    // Verificar utilizador
    $user = dbFetch("SELECT * FROM users WHERE email = ? AND status = 'active'", [$email]);
    
    if (!$user || !password_verify($password, $user['password'])) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Email ou password incorretos'
        ];
        redirect(SITE_URL . '/login' . $loginRedirectQ);
    }
    
    // Iniciar sessão
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    mergeGuestCartIntoUser((int) $user['id']);
    
    // Cookie de "lembrar-me"
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
        
        // Guardar token na base de dados
        dbUpdate('users', [
            'remember_token' => $token
        ], 'id = ?', [$user['id']]);
    }
    
    // Notificação
    $_SESSION['notification'] = [
        'type' => 'success',
        'message' => 'Bem-vindo, ' . $user['name'] . '!'
    ];
    
    $redirect = SITE_URL . '/conta';
    if (!empty($_SESSION['after_login_redirect'])) {
        $r = (string) $_SESSION['after_login_redirect'];
        unset($_SESSION['after_login_redirect']);
        if (strpos($r, SITE_URL) === 0) {
            $redirect = $r;
        }
    } elseif (!empty($_GET['redirect'])) {
        $r = (string) $_GET['redirect'];
        if (strpos($r, SITE_URL) === 0) {
            $redirect = $r;
        }
    }
    redirect($redirect);
}

// Incluir header
include_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<section class="breadcrumb">
    <div class="container">
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb-list">
                <li><a href="<?php echo SITE_URL; ?>">Início</a></li>
                <li>Iniciar Sessão</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Login Section -->
<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <h1>Iniciar Sessão</h1>
                <p>Bem-vindo de volta! Inicie sessão na sua conta.</p>
            </div>
            
            <form action="<?php echo SITE_URL; ?>/login<?php echo htmlspecialchars($loginRedirectQ); ?>" method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo $_POST['email'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember">
                        <span>Lembrar-me</span>
                    </label>
                    <a href="<?php echo SITE_URL; ?>/recuperar-password" class="forgot-password">Esqueceu a password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    Iniciar Sessão
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Não tem uma conta? <a href="<?php echo SITE_URL; ?>/registo<?php echo htmlspecialchars($loginRedirectQ); ?>">Registar</a></p>
            </div>
            
        </div>
    </div>
</section>

<?php
// Incluir footer
include_once __DIR__ . '/../includes/footer.php';
?>