<?php
// Nome  do Arquivo: login.php
// Função: Login de Clientes (Produção).
// Visual: Glassmorphism (Vidro) para manter o padrão Premium da Landing Page.

session_start();
require_once 'config.php';
require_once 'db.php';

// Se já logado, vai pro o painel
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
    <title>Área do Cliente | SGT</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Fonte Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; height: 100vh; overflow: hidden; display: flex; align-items: center; justify-content: center; }

        /* Fundo Engenharia (Mesmo da Landing Page) */
        .bg-wrapper {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1487958449943-2429e8be8625?q=80&w=2070&auto=format&fit=crop') no-repeat center center/cover;
            z-index: -1;
        }
        /* Máscara Escura (Para destacar o vidro) */
        .bg-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(14, 33, 48, 0.9) 0%, rgba(20, 70, 50, 0.85) 100%);
        }

        /* O CARTÃO DE VIDRO */
        .glass-login {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            text-align: center;
            color: white;
            animation: floatUp 0.8s ease-out;
        }

        @keyframes floatUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* Títulos */
        .glass-title { font-weight: 800; font-size: 1.8rem; letter-spacing: -0.5px; margin-bottom: 5px; }
        .glass-subtitle { font-size: 0.9rem; color: rgba(255,255,255,0.6); margin-bottom: 30px; }

        /* Inputs Translúcidos */
        .form-control-glass {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 1rem;
        }
        .form-control-glass:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #25D366;
            color: white;
            box-shadow: 0 0 15px rgba(37, 211, 102, 0.2);
        }
        /* Cor do placeholder */
        .form-control-glass::placeholder { color: rgba(255,255,255,0.4); }

        /* Botão Neon */
        .btn-neon {
            background-color: #25D366; color: white; border: none;
            width: 100%; padding: 12px; font-size: 1.1rem; font-weight: 700;
            border-radius: 8px; margin-top: 20px;
            box-shadow: 0 0 20px rgba(37, 211, 102, 0.4);
            transition: all 0.3s;
        }
        .btn-neon:hover {
            background-color: #128C7E; transform: scale(1.02);
            box-shadow: 0 0 30px rgba(37, 211, 102, 0.6);
        }

        /* Links do Rodapé */
        .footer-links { margin-top: 25px; font-size: 0.85rem; color: rgba(255,255,255,0.5); border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; }
        .footer-links a { color: rgba(255,255,255,0.8); text-decoration: none; transition: 0.3s; }
        .footer-links a:hover { color: white; }
        
        .divider { display: inline-block; margin: 0 10px; opacity: 0.3; }
    </style>
</head>
<body>

    <div class="bg-wrapper"><div class="bg-overlay"></div></div>

    <div class="glass-login">
        <div class="mb-3">
            <i class="bi bi-person-workspace fs-1 text-success"></i>
        </div>
        <h2 class="glass-title">Área do Cliente</h2>
        <p class="glass-subtitle">Acesso seguro ao ambiente de produção</p>
        
        <?php if($erro): ?>
            <div class="alert alert-danger py-2 small bg-danger bg-opacity-25 border border-danger text-white mb-4">
                <?php echo $erro; ?>
            </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="mb-3 text-start">
                <label class="small fw-bold text-white-50 mb-1 ps-1">LOGIN</label>
                <input type="text" name="usuario" class="form-control form-control-glass" required autofocus autocomplete="username" placeholder="seu@email.com">
            </div>
            
            <div class="mb-4 text-start">
                <label class="small fw-bold text-white-50 mb-1 ps-1">SENHA</label>
                <input type="password" name="senha" class="form-control form-control-glass" required autocomplete="current-password" placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-neon">ACESSAR SISTEMA</button>
        </form>

        <div class="footer-links">
            <a href="index.php">Voltar ao Site</a>
            <span class="divider">|</span>
            <a href="contratar.php" class="fw-bold text-success">Ver Planos</a>
        </div>
    </div>

</body>
</html>  