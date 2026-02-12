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
    <!-- CSS Premium -->
    <link rel="stylesheet" href="assets/css/auth-premium.css">
    <!-- Icons (Optional but recommended) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>

<div class="ambient-glow"></div>

<main class="auth-container">
    <section class="auth-card">
        <header class="auth-header">
            <h1>
                <div class="icon-logo">SGT</div>
                SGT Propostas
            </h1>
            <p>Acesso Administrativo</p>
        </header>

        <?php if ($erro): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div><?= htmlspecialchars($erro) ?></div>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <div class="form-group">
                <label for="usuario" class="form-label">Usuário</label>
                <input type="text" name="usuario" id="usuario" class="form-control" required placeholder="Seu usuário" autocomplete="username">
            </div>

            <div class="form-group">
                <label for="senha" class="form-label">Senha</label>
                <div class="input-group">
                    <input type="password" name="senha" id="senha" class="form-control" required placeholder="Sua senha" autocomplete="current-password" style="padding-right: 50px;">
                    <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Mostrar senha">
                        <i class="bi bi-eye" id="eye-icon"></i>
                        <i class="bi bi-eye-slash" id="eye-slash-icon" style="display: none;"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary">
                Entrar <i class="bi bi-arrow-right"></i>
            </button>
        </form>
        
        <footer class="auth-footer">
            <a href="index.php" class="link-secondary">Voltar ao site</a>
        </footer>
    </section>
</main>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('senha');
        const eyeIcon = document.getElementById('eye-icon');
        const eyeSlashIcon = document.getElementById('eye-slash-icon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.style.display = 'none';
            eyeSlashIcon.style.display = 'inline-block';
        } else {
            passwordInput.type = 'password';
            eyeIcon.style.display = 'inline-block';
            eyeSlashIcon.style.display = 'none';
        }
    }
</script>

</body>
</html>
