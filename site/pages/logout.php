<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Página de Logout
 */

// Incluir ficheiros de configuração e base de dados
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';

// Limpar sessão
$_SESSION = [];

// Destruir sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Limpar cookie de "lembrar-me"
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Notificação
session_start();
$_SESSION['notification'] = [
    'type' => 'success',
    'message' => 'Sessão terminada com sucesso!'
];

// Redirecionar para página inicial
redirect(SITE_URL);
?>