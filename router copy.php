<?php
// Nome da página: router.php
session_start();

// 1. Se não estiver logado, manda pro Login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// 2. Verifica o status do Setup
// Se setup_concluido == 0 -> Vai para o Assistente (Passo 1)
// Se setup_concluido == 1 -> Vai para o Painel Principal (index.php)

if ($_SESSION['setup_concluido'] == 0) {
    header("Location: setup.php?etapa=1");
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>