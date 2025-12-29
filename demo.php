<?php
// Nome do Arquivo: demo.php
// Função: Porta de Entrada Secundária (DEMO). Para leads e testes.

session_start();
require_once 'config.php';
require_once 'db.php';

// Se já logado, redireciona
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha   = trim($_POST['senha'] ?? '');

    if (empty($usuario) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    } else {
        try {
            // CONEXÃO DIRETA COM DEMO
            $conn = Database::getDemo();
            
            $stmt = $conn->prepare("SELECT id_usuario, usuario, senha, nome_completo, tipo_perfil FROM Usuarios WHERE usuario = ? LIMIT 1");
            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && $user['senha'] === $senha) {
                session_regenerate_id(true);

                $_SESSION['usuario_id']    = $user['id_usuario'];
                $_SESSION['usuario_nome']  = $user['nome_completo'];
                $_SESSION['usuario_login'] = $user['usuario'];
                $_SESSION['perfil']        = $user['tipo_perfil'];
                
                // DEFINE AMBIENTE DEMO
                $_SESSION['ambiente']      = 'demo'; 

                header("Location: index.php");
                exit;
            } else {
                $erro = "Login de teste inválido.";
            }

        } catch (Exception $e) {
            $erro = "Erro técnico: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Grátis | <?php echo defined('SITE_NAME') ? SITE_NAME : 'SGT'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%); display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
        .login-card { max-width: 400px; width: 95%; border-radius: 15px; box-shadow: 0 15px 30px rgba(0,0,0,0.3); overflow: hidden; }
        .login-header { background-color: #fff; color: #0d6efd; padding: 30px 20px 10px; text-align: center; }
        .btn-custom { background-color: #0d6efd; color: white; font-weight: bold; }
        .btn-custom:hover { background-color: #0b5ed7; color: white; }
    </style>
</head>
<body>

    <div class="card login-card bg-white border-0">
        <div class="login-header">
            <h3 class="mb-1 fw-bold">Teste Grátis</h3>
            <p class="text-muted small">Acesso ao Ambiente de Demonstração</p>
        </div>
        <div class="card-body p-4">
            
            <?php if($erro): ?>
                <div class="alert alert-warning text-center py-2"><?php echo $erro; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold text-secondary">E-mail Cadastrado</label>
                    <input type="text" name="usuario" class="form-control form-control-lg bg-light" placeholder="exemplo@email.com" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold text-secondary">Senha</label>
                    <input type="password" name="senha" class="form-control form-control-lg bg-light" placeholder="******" required>
                </div>

                <button type="submit" class="btn btn-custom w-100 btn-lg shadow-sm mb-3">
                    ACESSAR DEMO
                </button>
            </form>
            
            <div class="text-center border-top pt-3">
                <a href="criar_conta_demo.php" class="fw-bold text-decoration-none" style="color: #0d6efd;">Ainda não tem conta? Criar Agora</a>
            </div>
            
            <div class="text-center mt-3">
                <a href="login.php" class="text-muted small text-decoration-none">Sou cliente oficial (Login)</a>
            </div>
        </div>
    </div>

</body>
</html>