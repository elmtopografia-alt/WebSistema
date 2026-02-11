<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';

$erro = '';
$sucesso = '';

$db = new Database();

// Verificar se existe algum usuário admin
$totalAdmins = $db->query("SELECT COUNT(*) as total FROM usuarios_admin")->fetch()['total'];

// Se não houver usuários, permitir criação do primeiro admin
$primeiroAcesso = ($totalAdmins == 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Criar Primeiro Admin
    if ($primeiroAcesso && isset($_POST['criar_admin'])) {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $senha = $_POST['senha'];
        $confirmar = $_POST['confirmar_senha'];
        
        if ($senha !== $confirmar) {
            $erro = "As senhas não coincidem!";
        } elseif (strlen($senha) < 6) {
            $erro = "A senha deve ter pelo menos 6 caracteres.";
        } else {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios_admin (nome, email, senha, nivel, ativo) VALUES (?, ?, ?, 'admin', 1)";
            
            try {
                $stmt = $db->query($sql, [$nome, $email, $senhaHash]);
                $sucesso = "Administrador criado com sucesso! Faça login.";
                $primeiroAcesso = false;
            } catch (Exception $e) {
                $erro = "Erro ao criar admin: " . $e->getMessage();
            }
        }
    } 
    // Login Normal
    else {
        $email = trim($_POST['email']);
        $senha = $_POST['senha'];
        
        try {
            $user = $db->query("SELECT * FROM usuarios_admin WHERE email = ? AND ativo = 1 LIMIT 1", [$email])->fetch();
            
            if ($user && password_verify($senha, $user['senha'])) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_nome'] = $user['nome'];
                $_SESSION['admin_nivel'] = $user['nivel'];
                $_SESSION['last_activity'] = time();
                
                header('Location: painel.php');
                exit;
            } else {
                $erro = "E-mail ou senha incorretos.";
            }
        } catch (Exception $e) {
            $erro = "Erro no login: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GEOMETRPOLE</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f7fa;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .logo {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #2980b9;
        }
        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">GEOMETRPOLE Admin</div>
        
        <?php if ($erro): ?>
            <div class="alert alert-error"><?php echo $erro; ?></div>
        <?php endif; ?>
        
        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?php echo $sucesso; ?></div>
        <?php endif; ?>

        <?php if ($primeiroAcesso): ?>
            <h3 style="text-align:center; color:#27ae60;">Crie seu Administrador</h3>
            <form method="POST">
                <input type="hidden" name="criar_admin" value="1">
                <div class="form-group">
                    <label>Nome Completo</label>
                    <input type="text" name="nome" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" name="senha" class="form-control" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirmar Senha</label>
                    <input type="password" name="confirmar_senha" class="form-control" required minlength="6">
                </div>
                <button type="submit" class="btn" style="background:#27ae60;">Criar Admin</button>
            </form>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" name="senha" class="form-control" required>
                </div>
                <button type="submit" class="btn">Entrar</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
