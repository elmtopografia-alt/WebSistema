<?php
/**
 * login.php
 * Tela de autenticação
 */

require_once __DIR__ . '/database.php';

// Sessão SEMPRE depois do config
session_start();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario = trim($_POST['usuario'] ?? '');
    $senha   = $_POST['senha'] ?? '';

    if ($usuario && $senha) {

        $stmt = $pdo->prepare("
            SELECT id_usuario, senha, tipo_perfil, ambiente
            FROM Usuarios
            WHERE usuario = :usuario
            LIMIT 1
        ");
        $stmt->execute(['usuario' => $usuario]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['senha'])) {

            $_SESSION['id_usuario']  = $user['id_usuario'];
            $_SESSION['perfil']      = $user['tipo_perfil'];
            $_SESSION['ambiente']    = $user['ambiente'];

            header('Location: dashboard.php');
            exit;

        } else {
            $erro = 'Usuário ou senha inválidos.';
        }
    } else {
        $erro = 'Preencha todos os campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login | <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/login.css">
</head>
<body>

<div class="login-container">
    <h1><?= SITE_NAME ?></h1>

    <?php if ($erro): ?>
        <div class="erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <label>Usuário</label>
        <input type="text" name="usuario" required>

        <label>Senha</label>
        <input type="password" name="senha" required>

        <button type="submit">Entrar</button>
    </form>
</div>

</body>
</html>
