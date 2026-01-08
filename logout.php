<?php
// Nome do Arquivo: logout.php
// Função: Encerra sessão e redireciona para a página de login CORRESPONDENTE ao perfil que estava logado.

session_start();

// 1. Descobre para onde mandar
$destino = 'index.php'; // Padrão (Landing Page)

if (isset($_SESSION['origem_login'])) {
    if ($_SESSION['origem_login'] === 'admin') {
        $destino = 'login_admin.php';
    } elseif ($_SESSION['origem_login'] === 'demo') {
        $destino = 'login_demo.php';
    }
} elseif (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') {
    // Fallback se não tiver origem marcada
    $destino = 'login_demo.php';
}

// 2. Limpa tudo
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// 3. Tchau!
header("Location: $destino");
exit;
?>