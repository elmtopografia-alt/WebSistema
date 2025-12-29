<?php
// Nome do Arquivo: login.php
// Visual: NiceAdmin Standard (Bootstrap 5)

session_start();
require_once 'config.php';
require_once 'db.php';

// Se já logado, vai pro painel
if (isset($_SESSION['usuario_id'])) { header("Location: painel.php"); exit; }

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha   = trim($_POST['senha'] ?? '');

    if (empty($usuario) || empty($senha)) {
        $erro = "Preencha usuário e senha.";
    } else {
        try {
            $conn = Database::getProd();
            $stmt = $conn->prepare("SELECT id_usuario, usuario, senha, nome_completo, tipo_perfil, validade_acesso FROM Usuarios WHERE usuario = ? LIMIT 1");
            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($senha, $user['senha'])) {
                if ($user['tipo_perfil'] === 'admin') {
                    $erro = "Admins devem usar o <a href='login_admin.php'>Portal de Gestão</a>.";
                } else {
                    $hoje = new DateTime();
                    $val = new DateTime($user['validade_acesso'] ? $user['validade_acesso'] : '2000-01-01');
                    
                    if ($hoje > $val) {
                        $erro = "Assinatura vencida. <a href='contratar.php'>Renovar</a>.";
                    } else {
                        session_regenerate_id(true);
                        $_SESSION['usuario_id']    = $user['id_usuario'];
                        $_SESSION['usuario_nome']  = $user['nome_completo'];
                        $_SESSION['usuario_login'] = $user['usuario'];
                        $_SESSION['perfil']        = $user['tipo_perfil'];
                        $_SESSION['ambiente']      = 'producao'; 
                        $_SESSION['origem_login']  = 'cliente';
                        
                        header("Location: painel.php");
                        exit;
                    }
                }
            } else {
                $erro = "Credenciais inválidas.";
            }
        } catch (Exception $e) { $erro = "Erro técnico no sistema."; }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Login | SGT</title>

    <!-- Fontes e Bootstrap (CDN) -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- CSS Padronizado 
    <link href="assets/css/style.css" rel="stylesheet"> -->
     <link rel="stylesheet" href="../assets/css/estilo.css">
    
</head>

<body style="background-color: #f6f9ff;">

    <main>
        <div class="container">
            <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

                            <!-- Logo -->
                            <div class="d-flex justify-content-center py-4">
                                <a href="index.php" class="logo d-flex align-items-center w-auto text-decoration-none">
                                    <span class="d-none d-lg-block fs-3 text-primary fw-bold" style="font-family: 'Nunito', sans-serif;">SGT 2025</span>
                                </a>
                            </div>

                            <div class="card mb-3 shadow border-0" style="border-radius: 8px;">
                                <div class="card-body p-4">

                                    <div class="pt-4 pb-2">
                                        <h5 class="card-title text-center pb-0 fs-4" style="color:#012970; font-family:'Poppins', sans-serif;">Área do Cliente</h5>
                                        <p class="text-center small">Entre com seu e-mail e senha</p>
                                    </div>

                                    <?php if($erro): ?>
                                        <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                                            <i class="bi bi-exclamation-octagon me-1"></i> <?php echo $erro; ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    <?php endif; ?>

                                    <form class="row g-3" method="POST">

                                        <div class="col-12">
                                            <label for="usuario" class="form-label fw-bold small">E-mail</label>
                                            <div class="input-group has-validation">
                                                <input type="text" name="usuario" class="form-control" id="usuario" required>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label for="senha" class="form-label fw-bold small">Senha</label>
                                            <input type="password" name="senha" class="form-control" id="senha" required>
                                        </div>

                                        <div class="col-12 mt-4">
                                            <button class="btn btn-primary w-100" type="submit" style="background-color: #4154f1; border:none;">Entrar</button>
                                        </div>
                                        
                                        <div class="col-12">
                                            <p class="small mb-0">Não tem conta? <a href="contratar.php">Criar conta</a></p>
                                            <p class="small mb-0 text-center mt-2"><a href="index.php" class="text-muted">Voltar ao site</a></p>
                                        </div>
                                    </form>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>