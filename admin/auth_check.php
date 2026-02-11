<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';

// Verificar se usuário está logado
if (!isset($_SESSION['admin_id'])) {
    
    // Se não estiver logado, redirecionar para login
    // Mas antes, verificar se estamos em uma API Ajax
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('HTTP/1.1 401 Unauthorized');
        die(json_encode(['error' => 'Não autorizado']));
    }
    
    header('Location: login.php');
    exit;
}

// Verificar tempo de inatividade (30 minutos)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: login.php?msg=timeout');
    exit;
}
$_SESSION['last_activity'] = time();
?>
