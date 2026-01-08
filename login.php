<?php
/**
 * login.php
 * Tela de autenticação para Administradores
 */

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Se já está logado, redireciona para o painel
if (isset($_SESSION['usuario_id'])) {
    header('Location: painel.php');
    exit;
}

// Verifica se o ambiente foi definido no index.php (apenas admin)
if (!isset($_SESSION['ambiente']) || $_SESSION['ambiente'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha   = $_POST['senha'] ?? '';

    if (empty($usuario) || empty($senha)) {
        $erro = 'Preencha todos os campos.';
    } else {
        try {
            // Admin: conecta ao banco de produção
            $conn = Database::getProd();

            $stmt = $conn->prepare("SELECT id_usuario, usuario, senha, nome_completo, tipo_perfil FROM Usuarios WHERE usuario = ? LIMIT 1");
            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($senha, $user['senha'])) {
                // Admin: deve ter tipo_perfil = 'admin'
                if ($user['tipo_perfil'] !== 'admin') {
                    $erro = 'Acesso negado. Apenas administradores.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['usuario_id']    = $user['id_usuario'];
                    $_SESSION['usuario_nome']  = $user['nome_completo'];
                    $_SESSION['perfil']        = 'admin';
                    $_SESSION['ambiente']      = 'producao';
                    $_SESSION['origem_login']  = 'admin';
                    
                    header('Location: painel.php');
                    exit;
                }
            } else {
                $erro = 'Usuário ou senha inválidos.';
            }
        } catch (Exception $e) {
            $erro = 'Erro técnico. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | <?php echo defined('SITE_NAME') ? SITE_NAME : 'SGT'; ?></title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <h1><?php echo defined('SITE_NAME') ? SITE_NAME : 'SGT'; ?></h1>
            <p>Acesso Administrativo</p>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <div class="form-group">
                <label>Usuário</label>
                <input type="text" name="usuario" class="form-control" required placeholder="Seu usuário">
            </div>

            <div class="form-group">
                <label>Senha</label>
                <div class="password-wrapper">
                    <input type="password" name="senha" id="senha" class="form-control" required placeholder="Sua senha">
                    <span class="toggle-password" onclick="togglePassword()">
                        <!-- Icon Eye -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" id="eye-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <!-- Icon Eye Slash (hidden by default) -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" id="eye-slash-icon" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </span>
                </div>
            </div>

            <script>
                function togglePassword() {
                    const passwordInput = document.getElementById('senha');
                    const eyeIcon = document.getElementById('eye-icon');
                    const eyeSlashIcon = document.getElementById('eye-slash-icon');
                    
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        eyeIcon.style.display = 'none';
                        eyeSlashIcon.style.display = 'block';
                    } else {
                        passwordInput.type = 'password';
                        eyeIcon.style.display = 'block';
                        eyeSlashIcon.style.display = 'none';
                    }
                }
            </script>

            <button type="submit" class="btn-login">Entrar</button>
        </form>
        
        <div class="links">
            <a href="index.php">Voltar ao site</a>
        </div>
    </div>
</div>

</body>
</html>
