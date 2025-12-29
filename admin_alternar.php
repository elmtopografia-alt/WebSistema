<?php
// Nome do Arquivo: admin_alternar.php
// Função: Alterna entre PROD e DEMO e devolve para o PAINEL.

session_start();

// 1. Segurança: Só Admin ID 1
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_id'] != 1) {
    header("Location: painel.php");
    exit;
}

// 2. A Mágica: Inverte o Ambiente
if (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') {
    $_SESSION['ambiente'] = 'producao';
} else {
    $_SESSION['ambiente'] = 'demo';
}

// 3. Redirecionamento CORRETO (Para o Painel, não para a Home)
header("Location: painel.php");
exit;
?>