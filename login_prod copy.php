<?php
// Nome do Arquivo: login_prod.php
// Função: Login Oficial (Produção) com verificação de validade de assinatura.

session_start();
require_once 'config.php';
require_once 'db.php';

// Se já estiver logado, redireciona
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha   = trim($_POST['senha'] ?? '');

    if (empty($usuario) || empty($senha)) {
        $erro = "Informe usuário e senha.";
    } else {
        try {
            $conn = Database::getProd();
            
            // Busca dados e validade
            $stmt = $conn->prepare("SELECT id_usuario, usuario, senha, nome_completo, tipo_perfil, validade_acesso FROM Usuarios WHERE usuario = ? LIMIT 1");
            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && $user['senha'] === $senha) {
                
                // VERIFICAÇÃO DE VALIDADE DO PLANO
                // Admin (perfil admin) nunca vence
                if ($user['tipo_perfil'] !== 'admin') {
                    $hoje = new DateTime();
                    
                    // Se validade for nula, define uma data antiga para bloquear (segurança)
                    $dataValidade = $user['validade_acesso'] ? $user['validade_acesso'] : '2000-01-01';
                    $validade = new DateTime($dataValidade);
                    
                    if ($hoje > $validade) {
                        $erro = "Sua assinatura venceu em " . $validade->format('d/m/Y') . ".<br>Entre em contato para renovar.";
                    } else {
                        // Acesso Liberado
                        fazerLogin($user);
                    }
                } else {
                    // Admin Liberado
                    fazerLogin($user);
                }

            } else {
                $erro = "Dados incorretos.";
            }
        } catch (Exception $e) {
            $erro = "Erro técnico: " . $e->getMessage();
        }
    }
}

// Função auxiliar para registrar a sessão
function fazerLogin($user) {
    session_regenerate_id(true);
    $_SESSION['usuario_id']    = $user['id_usuario'];
    $_SESSION['usuario_nome']  = $user['nome_completo'];
    $_SESSION['usuario_login'] = $user['usuario'];
    $_SESSION['perfil']        = $user['tipo_perfil'];
    $_SESSION['ambiente']      = 'producao'; 
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login Cliente | Produção</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #198754; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
        .card-login { max-width: 400px; width: 90%; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="card card-login bg-white p-4">
        <div class="text-center mb-4">
            <h4 class="fw-bold text-success">Área do Cliente</h4>
            <p class="text-muted small">Acesso ao ambiente oficial</p>
        </div>
        
        <?php if($erro): ?><div class="alert alert-danger py-2 text-center small"><?php echo $erro; ?></div><?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold">Usuário</label>
                <input type="text" name="usuario" class="form-control bg-light" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Senha</label>
                <input type="password" name="senha" class="form-control bg-light" required>
            </div>
            <button type="submit" class="btn btn-success w-100 fw-bold btn-lg">ENTRAR</button>
        </form>
        <div class="text-center mt-3">
            <a href="login.php" class="text-decoration-none text-muted small">&larr; Voltar ao Início</a>
        </div>
    </div>
</body>
</html>