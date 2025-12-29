<?php
// Nome do Arquivo: login_demo.php
// Visual: NiceAdmin Standard

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
                $erro = "Teste expirado. <a href='contratar.php'>Contratar agora</a>.";
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
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Demo | SGT</title>
    <!-- CSS Padronizado -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body style="background-color: #f6f9ff;">
    <main>
        <div class="container">
            <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
                            
                            <div class="d-flex justify-content-center py-4">
                                <a href="index.php" class="logo d-flex align-items-center w-auto text-decoration-none">
                                    <span class="d-none d-lg-block fs-3 text-primary fw-bold" style="font-family: 'Nunito', sans-serif;">SGT 2025</span>
                                </a>
                            </div>

                            <div class="card mb-3 shadow border-0">
                                <div class="card-body p-4">
                                    <div class="pt-4 pb-2">
                                        <h5 class="card-title text-center pb-0 fs-4 text-warning">Ambiente de Teste</h5>
                                        <p class="text-center small">Acesso Gratuito (5 dias)</p>
                                    </div>

                                    <?php if($sucesso): ?><div class="alert alert-success py-2 small text-center"><?= $sucesso ?></div><?php endif; ?>
                                    <?php if($erro): ?><div class="alert alert-danger py-2 small text-center"><?= $erro ?></div><?php endif; ?>

                                    <form class="row g-3" method="POST">
                                        <div class="col-12">
                                            <label class="form-label fw-bold small">E-mail de Teste</label>
                                            <input type="text" name="usuario" class="form-control" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold small">Senha Criada</label>
                                            <input type="password" name="senha" class="form-control" required>
                                        </div>
                                        <div class="col-12 mt-4">
                                            <button class="btn btn-warning w-100 text-white fw-bold" type="submit">ACESSAR DEMO</button>
                                        </div>
                                        <div class="col-12 text-center mt-3">
                                            <p class="small mb-0">Ainda não criou? <a href="criar_conta_demo.php">Criar agora</a></p>
                                            <p class="small mt-2"><a href="index.php" class="text-muted">Voltar</a></p>
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
</body>
</html>