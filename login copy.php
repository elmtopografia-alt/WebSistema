<?php
/**
 * login.php
 * Página de login do sistema
 */
require_once __DIR__ . '/config.php';

// ==========================================================
// INICIAR SESSÃO (DEPOIS DO CONFIG)
// ==========================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================================
// PROCESSAMENTO DO LOGIN
// ==========================================================
$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario = trim($_POST['usuario'] ?? '');
    $senha   = $_POST['senha'] ?? '';

    if ($usuario === '' || $senha === '') {
        $erro = 'preencha todos os campos';
    } else {

        $sql = "
            SELECT id_usuario, usuario, senha, tipo_perfil, ambiente
            FROM Usuarios
            WHERE usuario = :usuario
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':usuario', $usuario);
        $stmt->execute();

        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['senha'])) {

            $_SESSION['usuario_id']   = $user['id_usuario'];
            $_SESSION['usuario_nome'] = $user['usuario'];
            $_SESSION['perfil']       = $user['tipo_perfil'];
            $_SESSION['ambiente']     = $user['ambiente'];

            header('Location: dashboard.php');
            exit;

        } else {
            $erro = 'usuário ou senha inválidos';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>login | <?= SITE_NAME ?></title>
</head>
<body>

<h2>acesso ao sistema</h2>

<?php if ($erro): ?>
    <p style="color:red"><?= htmlspecialchars($erro) ?></p>
<?php endif; ?>

<form method="post">
    <label>usuário</label><br>
    <input type="text" name="usuario"><br><br>

    <label>senha</label><br>
    <input type="password" name="senha"><br><br>

    <button type="submit">entrar</button>
</form>

</body>
</html>
