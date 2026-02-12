<?php
/**
 * index.php
 * Ponto único de entrada do sistema
 * Responsável por direcionar o usuário ao ambiente correto
 */

session_start();

// Se já estiver logado, redireciona conforme ambiente
if (isset($_SESSION['ambiente'])) {
    switch ($_SESSION['ambiente']) {
        case 'prod':
            header('Location: login_prod.php');
            exit;
        case 'demo':
            header('Location: login_demo.php');
            exit;
        case 'admin':
            header('Location: login_admin.php');
            exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SGT - Sistema de Gestão Topográfica</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap (herança visual garantida) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container vh-100 d-flex align-items-center justify-content-center">
    <div class="card shadow-lg p-4" style="max-width: 420px; width:100%;">
        <h4 class="text-center mb-4">acesso ao sistema</h4>

        <div class="d-grid gap-3">
            <a href="login_prod.php" class="btn btn-primary btn-lg">
                ambiente produção
            </a>

            <a href="login_demo.php" class="btn btn-secondary btn-lg">
                ambiente demonstração
            </a>

            <a href="login_admin.php" class="btn btn-dark btn-lg">
                administração
            </a>
        </div>

        <hr class="my-4">

        <p class="text-center text-muted small mb-0">
            sgt — sistema de gestão topográfica
        </p>
    </div>
</div>

</body>
</html>
