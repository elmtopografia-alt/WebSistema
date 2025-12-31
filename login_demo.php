<?php
// Nome do Arquivo: login_demo.php
// Função: Login Demo. Permite salvar senha para facilitar os 5 dias de teste.

session_start();
require_once 'config.php';
require_once 'db.php';

if (isset($_SESSION['usuario_id'])) { header("Location: painel.php"); exit; }

$erro = '';
$sucesso = (isset($_GET['msg']) && $_GET['msg']=='criada') ? "Conta criada! Use sua senha." : "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha   = trim($_POST['senha'] ?? '');

    try {
        $conn = Database::getDemo();
        $stmt = $conn->prepare("SELECT id_usuario, usuario, senha, nome_completo, tipo_perfil, validade_acesso FROM Usuarios WHERE usuario = ? LIMIT 1");
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($senha, $user['senha'])) {
            $hoje = new DateTime();
            $val = new DateTime($user['validade_acesso']);
            if ($hoje > $val) {
                $erro = "Teste expirado. <a href='contratar.php'>Contratar</a>.";
            } else {
                session_regenerate_id(true);
                $_SESSION['usuario_id']    = $user['id_usuario'];
                $_SESSION['usuario_nome']  = $user['nome_completo'];
                $_SESSION['perfil']        = 'cliente';
                $_SESSION['ambiente']      = 'demo';
                $_SESSION['origem_login']  = 'demo';
                
                header("Location: painel.php");
                exit;
            }
        } else {
            $erro = "Dados incorretos.";
        }
    } catch (Exception $e) { $erro = "Erro técnico."; }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Acesso Demo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background:#0d6efd;display:flex;align-items:center;justify-content:center;height:100vh}</style>
</head>
<body>
    <div class="card border-0 shadow-lg" style="width:400px">
        <div class="card-body p-4 bg-white rounded">
            <h4 class="text-primary text-center fw-bold mb-3">Ambiente de Teste</h4>
            <?php if($sucesso): ?><div class="alert alert-success py-1 small text-center"><?php echo $sucesso; ?></div><?php endif; ?>
            <?php if($erro): ?><div class="alert alert-warning py-1 small text-center"><?php echo $erro; ?></div><?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="fw-bold small">E-mail</label>
                    <input type="text" name="usuario" class="form-control bg-light" required autocomplete="username">
                </div>
                <div class="mb-3">
                    <label class="fw-bold small">Senha</label>
                    <input type="password" name="senha" class="form-control bg-light" required autocomplete="current-password">
                </div>
                <button class="btn btn-primary w-100 fw-bold shadow-sm">ACESSAR DEMO</button>
            </form>
            
            <div class="text-center mt-3 border-top pt-2">
                <a href="criar_conta_demo.php" class="fw-bold text-decoration-none">Criar Conta Grátis</a>
            </div>
            <div class="text-center mt-2"><a href="index.php" class="text-muted small text-decoration-none">Voltar ao site</a></div>
        </div>
    </div>
</body>
</html>