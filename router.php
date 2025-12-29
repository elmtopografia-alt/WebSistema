<?php
// router.php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Se não tiver setup, assume concluído para evitar loop se arquivo setup não existir
if (isset($_SESSION['setup_concluido']) && $_SESSION['setup_concluido'] == 0) {
    // header("Location: setup.php"); // Desativado pois setup.php não foi fornecido
    header("Location: index.php");
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>