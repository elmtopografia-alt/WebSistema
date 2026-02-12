<?php
// Nome do Arquivo: login_demo.php
// Função: Login Demo. Permite salvar senha para facilitar os 5 dias de teste.

// Configura parâmetros da sessão antes de iniciar
if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        $cookieParams = session_get_cookie_params();
        session_set_cookie_params([
            'lifetime' => $cookieParams['lifetime'],
            'path' => '/',
            'domain' => $cookieParams['domain'],
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    } else {
        @session_start();
    }
}
require_once 'config.php';
require_once 'db.php';

if (isset($_SESSION['usuario_id'])) { 
    if(isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') {
        header("Location: Cli_demo.php"); 
        exit; 
    } else {
        header("Location: Cli_Pro.php");
        exit;
    }
}

$erro = '';
$sucesso = (isset($_GET['msg']) && $_GET['msg']=='criada') ? "Conta criada com sucesso! Use sua senha abaixo." : "";

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
                $erro = "
                    <strong>Seu período de teste acabou!</strong><br>
                    Seus dados estão agendados para exclusão.<br>
                    <a href='contratar.php' class='btn btn-warning btn-sm mt-2 w-100 fw-bold'>SALVAR MEUS DADOS AGORA</a>
                ";
            } else {
                // session_regenerate_id(true); // Desativado para evitar perda de sessão no Windows
                $_SESSION['usuario_id']    = $user['id_usuario'];
                $_SESSION['usuario_nome']  = $user['nome_completo'];
                $_SESSION['perfil']        = 'cliente';
                $_SESSION['ambiente']      = 'demo';
                $_SESSION['origem_login']  = 'demo';
                
                $_SESSION['validade_demo'] = $user['validade_acesso'];
                
                // Força a gravação da sessão antes de redirecionar
                session_write_close();
                header("Location: Cli_demo.php");
                exit;
            }
        } else {
            $erro = "Dados incorretos ou conta inexistente.";
        }
    } catch (Exception $e) { $erro = "Erro técnico no servidor."; }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT | Área Demo</title>
    <!-- Premium Auth CSS -->
    <link rel="stylesheet" href="assets/css/auth-premium.css">
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        /* Override/Extend for Split Layout */
        body {
            display: block; /* Reset centering */
            overflow-y: auto;
        }

        .split-container {
            min-height: 100vh;
            display: flex;
            background: radial-gradient(circle at center, #0f172a 0%, #1e293b 100%);
        }

        /* Left Side (Info) */
        .info-side {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }
        
        /* Right Side (Login) */
        .login-side {
            width: 100%;
            max-width: 500px;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px;
            box-shadow: -10px 0 30px rgba(0,0,0,0.1);
            position: relative;
            z-index: 20;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .split-container {
                flex-direction: column;
            }
            .info-side {
                display: none; /* Simplify on mobile */
            }
            .login-side {
                max-width: 100%;
                min-height: 100vh;
            }
        }

        /* Demo Features */
        .feature-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 24px;
            color: rgba(255,255,255,0.9);
        }
        .feature-icon {
            font-size: 24px;
            color: var(--warning);
            margin-right: 16px;
            margin-top: 2px;
        }
        .feature-title {
            display: block;
            font-weight: 700;
            color: white;
            margin-bottom: 4px;
            font-size: 1.1rem;
        }
        .feature-desc {
            font-size: 0.95rem;
            line-height: 1.5;
            color: #cbd5e1;
        }

        /* Demo Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 24px;
            max-width: 600px;
        }

        .cta-box {
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 20px;
            margin-top: 30px;
            text-align: center;
        }
        
        .btn-create-account {
            background: white;
            color: #0f172a;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            transition: transform 0.2s;
        }
        .btn-create-account:hover {
            transform: scale(1.02);
            color: var(--primary);
        }
    </style>
</head>
<body>

    <div class="split-container">
        
        <!-- Left Side: Marketing -->
        <div class="info-side">
            <div class="ambient-glow" style="position: absolute;"></div>
            
            <div class="glass-card">
                <h1 style="color: white; font-size: 2.5rem; margin-bottom: 20px; font-weight: 800;">
                    Área de Teste <span style="color: var(--warning)">DEMO 🧪</span>
                </h1>
                <p style="color: #cbd5e1; font-size: 1.1rem; margin-bottom: 40px;">
                    Experimente todo o poder do SGT Propostas gratuitamente por 5 dias.
                </p>

                <div class="feature-item">
                    <i class="ph ph-lightning feature-icon"></i>
                    <div>
                        <span class="feature-title">Funcionalidades Completas</span>
                        <span class="feature-desc">Teste 100% dos recursos: criação de propostas, dashboards financeiros e gestão.</span>
                    </div>
                </div>

                <div class="feature-item">
                    <i class="ph ph-handshake feature-icon"></i>
                    <div>
                        <span class="feature-title">Sem Compromisso</span>
                        <span class="feature-desc">Não é necessário cartão de crédito. Acesso liberado imediatamente.</span>
                    </div>
                </div>

                <div class="cta-box">
                    <p style="color: white; margin-bottom: 10px; font-weight: 600;">Não tem conta ainda?</p>
                    <a href="criar_conta_demo.php" class="btn-create-account">
                        <i class="ph ph-user-plus"></i> CRIAR CONTA GRÁTIS AGORA
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="login-side">
            <div style="max-width: 400px; margin: 0 auto; width: 100%;">
                
                <header class="auth-header" style="text-align: left;">
                    <h1>Login Demonstração</h1>
                    <p>Acesse sua conta de teste</p>
                </header>

                <!-- Warning Banner -->
                <div class="alert alert-warning" role="alert" style="flex-direction: column; gap: 5px;">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <i class="ph ph-clock-countdown" style="font-size: 24px; color: var(--warning-dark);"></i>
                        <strong>SEU ACESSO VALE POR 5 DIAS</strong>
                    </div>
                    <div style="font-size: 0.85rem; opacity: 0.9;">
                        Ao término, seus dados ficam salvos por apenas 30 dias.
                    </div>
                </div>

                <?php if($sucesso): ?>
                    <div class="alert alert-success">
                        <i class="ph ph-check-circle"></i> <?= htmlspecialchars($sucesso) ?>
                    </div>
                <?php endif; ?>

                <?php if($erro): ?>
                    <div class="alert alert-danger">
                        <i class="ph ph-warning"></i> 
                        <div><?= $erro ?></div> <! -- $erro pode conter HTML seguro aqui -->
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="usuario" class="form-label">E-MAIL DE CADASTRO</label>
                        <input type="text" name="usuario" id="usuario" class="form-control" placeholder="email@exemplo.com" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="senha" class="form-label">SENHA</label>
                        <input type="password" name="senha" id="senha" class="form-control" placeholder="******" required>
                    </div>

                    <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #f97316, #ea580c); box-shadow: 0 4px 6px -1px rgba(234, 88, 12, 0.3);">
                        <i class="ph ph-sign-in"></i> ACESSAR DEMO
                    </button>
                </form>

                <div style="text-align: center; margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                    <a href="index.php" class="link-secondary" style="font-size: 0.9rem;">
                        <i class="ph ph-arrow-left"></i> Voltar ao Site
                    </a>
                </div>

            </div>
        </div>

    </div>

</body>
</html>