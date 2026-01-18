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
            if ($hoje > $val) {
                $erro = "
                    <strong>Seu período de teste acabou!</strong><br>
                    Seus dados estão agendados para exclusão.<br>
                    <a href='contratar.php' class='btn btn-warning btn-sm mt-2 w-100 fw-bold'>SALVAR MEUS DADOS AGORA</a>
                ";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Demo</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Ambiente de Teste</h1>
                <p>Acesse sua conta demo</p>
            </div>

            <?php if($sucesso): ?>
                <div class="alert alert-success"><?php echo $sucesso; ?></div>
            <?php endif; ?>
            
            <?php if($erro): ?>
                <div class="alert alert-danger"><?php echo $erro; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="text" name="usuario" class="form-control" required autocomplete="username" placeholder="Seu e-mail">
                </div>
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" name="senha" class="form-control" required autocomplete="current-password" placeholder="Sua senha">
                </div>
                <button class="btn-login">ACESSAR DEMO</button>
            </form>
            
            <div class="links">
                <a href="criar_conta_demo.php">Criar Conta Grátis</a>
                <br><br>
                <a href="index.php" style="color: #888;">Voltar ao site</a>
            </div>
        </div>
    </div>
</body>
</html>