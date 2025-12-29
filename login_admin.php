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
                    
                    header("Location: painel.php");
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
    <title>SGT Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background:#212529;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh}</style>
</head>
<body>
    <div class="card bg-dark border-secondary shadow-lg" style="width:350px">
        <div class="card-header text-center border-secondary"><h5 class="text-warning mb-0 fw-bold">ADMINISTRAÇÃO</h5></div>
        <div class="card-body p-4">
            <?php if($erro): ?><div class="alert alert-danger py-1 small text-center"><?php echo $erro; ?></div><?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <input type="text" name="usuario" class="form-control bg-secondary text-white border-0" placeholder="Usuário" required autocomplete="username">
                </div>
                <div class="mb-3">
                    <input type="password" name="senha" class="form-control bg-secondary text-white border-0" placeholder="Senha" required autocomplete="current-password">
                </div>
                <button class="btn btn-warning w-100 fw-bold">ACESSAR</button>
            </form>
        </div>
    </div>
</body>
</html>