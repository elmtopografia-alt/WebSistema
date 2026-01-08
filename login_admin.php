<?php
// Nome do Arquivo: login_admin.php
// Função: Acesso ADMIN. Permite salvar senha.

session_start();
require_once 'config.php';
require_once 'db.php';

if (isset($_SESSION['usuario_id'])) { header("Location: painel.php"); exit; }

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha   = trim($_POST['senha'] ?? '');

    if (empty($usuario) || empty($senha)) {
        $erro = "Informe credenciais.";
    } else {
        try {
            $conn = Database::getProd(); 
            $stmt = $conn->prepare("SELECT id_usuario, usuario, senha, nome_completo, tipo_perfil FROM Usuarios WHERE usuario = ? LIMIT 1");
            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if ($user && password_verify($senha, $user['senha'])) {
                if ($user['tipo_perfil'] !== 'admin') {
                    $erro = "Acesso negado.";
                } else {
                    session_regenerate_id(true);
                    $_SESSION['usuario_id']    = $user['id_usuario'];
                    $_SESSION['usuario_nome']  = $user['nome_completo'];
                    $_SESSION['perfil']        = 'admin';
                    $_SESSION['ambiente']      = 'producao'; 
                    $_SESSION['origem_login']  = 'admin';
                    
                    $_SESSION['origem_login']  = 'admin';
                    
                    $redirect = $_POST['redirect'] ?? 'painel.php';
                    // Sanitização básica para evitar open redirect
                    $redirect = preg_replace('/[^a-zA-Z0-9_.]/', '', $redirect);
                    if (empty($redirect) || !file_exists(__DIR__ . '/' . $redirect)) {
                        $redirect = 'painel.php';
                    }

                    header("Location: " . $redirect);
                    exit;
                }
            } else {
                $erro = "Acesso negado.";
            }
        } catch (Exception $e) { $erro = "Erro técnico."; }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT Admin</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>ADMINISTRAÇÃO</h1>
                <p>Acesso restrito</p>
            </div>

            <?php if($erro): ?>
                <div class="alert alert-danger"><?php echo $erro; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Usuário</label>
                    <input type="text" name="usuario" class="form-control" placeholder="Usuário" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" name="senha" class="form-control" placeholder="Senha" required autocomplete="current-password">
                </div>
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect'] ?? ''); ?>">
                <button class="btn-login">ACESSAR</button>
            </form>
        </div>
    </div>
</body>
</html>