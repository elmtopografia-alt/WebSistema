<?php
// Nome do Arquivo: login.php
// Função: Login de Clientes (Produção).
// Visual: Glassmorphism com Copywriting de Boas-vindas focado em Negócios.

session_start();
require_once 'config.php';
require_once 'db.php';

// Se já logado, vai pro painel
if (isset($_SESSION['usuario_id'])) {
    header("Location: painel.php");
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha   = trim($_POST['senha'] ?? '');

    if (empty($usuario) || empty($senha)) {
        $erro = "Preencha usuário e senha.";
    } else {
        try {
            // CONEXÃO BLINDADA: Só olha no banco de PRODUÇÃO
            $conn = Database::getProd();
            
            $stmt = $conn->prepare("SELECT id_usuario, usuario, senha, nome_completo, tipo_perfil, validade_acesso FROM Usuarios WHERE usuario = ? LIMIT 1");
            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($senha, $user['senha'])) {
                
                // --- TRAVA DE SEGURANÇA ---
                if ($user['tipo_perfil'] === 'admin') {
                    $erro = "Administradores devem usar o <a href='login_admin.php' class='text-warning fw-bold'>Portal de Gestão</a>.";
                } else {
                    // Verifica validade
                    $hoje = new DateTime();
                    $val = new DateTime($user['validade_acesso'] ? $user['validade_acesso'] : '2000-01-01');
                    
                    if ($hoje > $val) {
                        $erro = "Sua assinatura venceu. <a href='contratar.php' class='text-white fw-bold'>Renovar agora</a>.";
                    } else {
                        // Login Sucesso
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
                $erro = "Usuário ou senha incorretos.";
            }
        } catch (Exception $e) { $erro = "Erro técnico no sistema."; }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo | SGT</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Fonte Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; height: 100vh; overflow: hidden; display: flex; align-items: center; justify-content: center; }

        /* Fundo Engenharia */
        .bg-wrapper {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1487958449943-2429e8be8625?q=80&w=2070&auto=format&fit=crop') no-repeat center center/cover;
            z-index: -1;
        }
        /* Máscara Escura */
        .bg-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(14, 33, 48, 0.92) 0%, rgba(20, 70, 50, 0.88) 100%);
        }

        /* O CARTÃO DE VIDRO */
        .glass-login {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 45px 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            text-align: center;
            color: white;
            animation: floatUp 0.8s ease-out;
        }

        @keyframes floatUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* Títulos de Boas-Vindas */
        .welcome-title { 
            font-weight: 900; 
            font-size: 2.2rem; 
            letter-spacing: 1px; 
            margin-bottom: 5px; 
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .welcome-subtitle { 
            font-size: 0.95rem; 
            color: #25D366; /* Verde SGT */
            text-transform: uppercase; 
            letter-spacing: 2px;
            font-weight: 700;
            margin-bottom: 35px; 
            opacity: 0.9;
        }

        /* Inputs Translúcidos */
        .form-control-glass {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: white;
            padding: 14px 15px;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .form-control-glass:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #25D366;
            color: white;
            box-shadow: 0 0 20px rgba(37, 211, 102, 0.2);
            outline: none;
        }
        .form-control-glass::placeholder { color: rgba(255,255,255,0.4); }

        /* Botão Neon */
        .btn-neon {
            background-color: #25D366; color: white; border: none;
            width: 100%; padding: 14px; font-size: 1.1rem; font-weight: 800;
            border-radius: 50px; margin-top: 10px;
            box-shadow: 0 0 20px rgba(37, 211, 102, 0.4);
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-neon:hover {
            background-color: #128C7E; transform: scale(1.02);
            box-shadow: 0 0 35px rgba(37, 211, 102, 0.6);
        }

        /* Links do Rodapé */
        .footer-links { margin-top: 30px; font-size: 0.85rem; color: rgba(255,255,255,0.5); border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; }
        .footer-links a { color: rgba(255,255,255,0.8); text-decoration: none; transition: 0.3s; }
        .footer-links a:hover { color: white; }
        
        .divider { display: inline-block; margin: 0 10px; opacity: 0.3; }
        
        /* Ícone decorativo */
        .icon-top { font-size: 2.5rem; color: rgba(255,255,255,0.8); margin-bottom: 15px; display: inline-block; }
    </style>
</head>
<body>

    <div class="bg-wrapper"><div class="bg-overlay"></div></div>

    <div class="glass-login">
        
        <!-- Cabeçalho Poderoso -->
        <h1 class="welcome-title">BEM-VINDO.</h1>
        <p class="welcome-subtitle">ÓTIMOS NEGÓCIOS COMEÇAM AQUI.</p>
        
        <?php if($erro): ?>
            <div class="alert alert-danger py-2 small bg-danger bg-opacity-25 border border-danger text-white mb-4 rounded-3">
                <i class="bi bi-exclamation-circle me-2"></i> <?php echo $erro; ?>
            </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="mb-3 text-start">
                <input type="text" name="usuario" class="form-control form-control-glass" required autofocus autocomplete="username" placeholder="Seu E-mail">
            </div>
            
            <div class="mb-4 text-start">
                <input type="password" name="senha" class="form-control form-control-glass" required autocomplete="current-password" placeholder="Sua Senha">
            </div>

            <button type="submit" class="btn btn-neon">
                Entrar no Sistema <i class="bi bi-arrow-right-short"></i>
            </button>
        </form>

        <div class="footer-links">
            <a href="index.php">Voltar ao Site</a>
            <span class="divider">|</span>
            <a href="contratar.php" class="fw-bold text-success">Ver Planos</a>
        </div>
    </div>

</body>
</html>