<?php
// Nome do Arquivo: login_demo.php
// Função: Login para usuários de teste (Banco: Proposta).

session_start();
require_once 'config.php';
require_once 'db.php';

if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$erro = '';
$sucesso = '';

// Mensagem de sucesso vinda do cadastro
if (isset($_GET['msg']) && $_GET['msg'] === 'criada') {
    $sucesso = "Conta criada com sucesso! Digite sua senha abaixo para entrar.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha   = trim($_POST['senha'] ?? '');

    if (empty($usuario) || empty($senha)) {
        $erro = "Preencha e-mail e senha.";
    } else {
        try {
            // Conecta no Banco de DEMO
            $conn = Database::getDemo();
            
            $stmt = $conn->prepare("SELECT id_usuario, usuario, senha, nome_completo, tipo_perfil, validade_acesso FROM Usuarios WHERE usuario = ? LIMIT 1");
            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && $user['senha'] === $senha) {
                
                // Verifica validade (ex: 5 dias)
                $agora = new DateTime();
                $validade = new DateTime($user['validade_acesso']);
                
                if ($agora > $validade) {
                    $erro = "O período de teste desta conta expirou.";
                } else {
                    session_regenerate_id(true);
                    $_SESSION['usuario_id']    = $user['id_usuario'];
                    $_SESSION['usuario_nome']  = $user['nome_completo'];
                    $_SESSION['perfil']        = $user['tipo_perfil'];
                    $_SESSION['ambiente']      = 'demo'; 

                    header("Location: index.php");
                    exit;
                }
            } else {
                $erro = "E-mail ou senha incorretos.";
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
    <title>Login Demo | Teste Grátis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0d6efd; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card-login { max-width: 400px; width: 90%; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="card card-login bg-white p-4">
        <div class="text-center mb-4">
            <h4 class="fw-bold text-primary">Ambiente de Teste</h4>
            <p class="text-muted small">Use seu e-mail e senha cadastrados.</p>
        </div>
        
        <?php if($sucesso): ?><div class="alert alert-success py-2 small"><?php echo $sucesso; ?></div><?php endif; ?>
        <?php if($erro): ?><div class="alert alert-danger py-2 small"><?php echo $erro; ?></div><?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold">E-mail Cadastrado</label>
                <input type="email" name="usuario" class="form-control bg-light" required placeholder="exemplo@email.com">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Senha</label>
                <input type="password" name="senha" class="form-control bg-light" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold btn-lg">ENTRAR NA DEMO</button>
        </form>
        
        <div class="text-center mt-3 pt-3 border-top">
            <a href="criar_conta_demo.php" class="text-primary fw-bold text-decoration-none">Não tem senha? Cadastre-se</a>
        </div>
        <div class="text-center mt-2">
            <a href="login.php" class="text-decoration-none text-muted small">&larr; Voltar ao Início</a>
        </div>
    </div>
</body>
</html>