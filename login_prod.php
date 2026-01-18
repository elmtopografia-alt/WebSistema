<?php
// Nome do Arquivo: login_prod.php
// Função: Login Clientes com MIGRAÇÃO AUTOMÁTICA PARA HASH (Segurança Bancária).

session_start();
require_once 'config.php';
require_once 'db.php';

if (isset($_SESSION['usuario_id'])) { header("Location: painel.php"); exit; }

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitização: Remove caracteres perigosos do usuário
    $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_SPECIAL_CHARS);
    $senha   = $_POST['senha']; // Senha não se sanitiza, pois pode ter símbolos propositais

    if (empty($usuario) || empty($senha)) {
        $erro = "Preencha usuário e senha.";
    } else {
        try {
            $conn = Database::getProd();
            
            // Busca o usuário pelo Login (Email ou Nome de Usuário)
            $stmt = $conn->prepare("SELECT id_usuario, usuario, senha, nome_completo, tipo_perfil, validade_acesso FROM Usuarios WHERE usuario = ? LIMIT 1");
            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                $senha_valida = false;
                $precisa_migrar = false;

                // 1. Tenta verificar como HASH (O jeito novo e seguro)
                if (password_verify($senha, $user['senha'])) {
                    $senha_valida = true;
                } 
                // 2. Se falhar, tenta como TEXTO PURO (Para usuários antigos não ficarem trancados)
                elseif ($user['senha'] === $senha) {
                    $senha_valida = true;
                    $precisa_migrar = true; // Marca para atualizar a segurança
                }

                if ($senha_valida) {
                    // VERIFICAÇÃO ADMINISTRATIVA (Admin não entra aqui)
                    if ($user['tipo_perfil'] === 'admin') {
                        $erro = "Acesso negado. Admins devem usar a porta de gestão.";
                    } else {
                        // VERIFICA VALIDADE
                        $hoje = new DateTime();
                        $val = new DateTime($user['validade_acesso'] ? $user['validade_acesso'] : '2000-01-01');
                        
                        if ($hoje > $val) {
                            $erro = "Sua assinatura venceu. Entre em contato.";
                        } else {
                            
                            // *** A MÁGICA DA SEGURANÇA ***
                            // Se a senha era velha (texto), criptografa AGORA e salva
                            if ($precisa_migrar) {
                                $novo_hash = password_hash($senha, PASSWORD_DEFAULT);
                                $upd = $conn->prepare("UPDATE Usuarios SET senha = ? WHERE id_usuario = ?");
                                $upd->bind_param('si', $novo_hash, $user['id_usuario']);
                                $upd->execute();
                            }

                            // Login Sucesso
                            session_regenerate_id(true);
                            $_SESSION['usuario_id']    = $user['id_usuario'];
                            $_SESSION['usuario_nome']  = $user['nome_completo'];
                            $_SESSION['perfil']        = $user['tipo_perfil'];
                            $_SESSION['ambiente']      = 'producao'; 
                            $_SESSION['origem_login']  = 'cliente';
                            
                            header("Location: painel.php");
                            exit;
                        }
                    }
                } else {
                    $erro = "Senha incorreta.";
                }
            } else {
                $erro = "Usuário não encontrado.";
            }
        } catch (Exception $e) { $erro = "Erro técnico."; }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Cliente | <?php echo defined('SITE_NAME') ? SITE_NAME : 'SGT'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%); display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
        .login-card { width: 100%; border-radius: 15px; box-shadow: 0 15px 30px rgba(0,0,0,0.3); overflow: hidden; }
        .login-header { background-color: #fff; color: #0d6efd; padding: 30px 20px 10px; text-align: center; }
        .btn-custom { background-color: #0d6efd; color: white; font-weight: bold; }
        .btn-custom:hover { background-color: #0b5ed7; color: white; }
        .feature-item { margin-bottom: 20px; display: flex; align-items: flex-start; }
        .feature-icon { font-size: 1.5rem; margin-right: 15px; flex-shrink: 0; }
        .feature-text { color: white; font-size: 0.95rem; opacity: 0.9; }
        .feature-title { font-weight: bold; display: block; margin-bottom: 2px; font-size: 1rem; }
    </style>
</head>
<body>

    <div class="row justify-content-center w-100 px-3">
        <!-- Coluna de Informações PRO -->
        <div class="col-lg-5 col-md-8 mb-4 mb-lg-0">
            <div class="card h-100 border-0 shadow-lg" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px);">
                <div class="card-body p-4 p-lg-5 text-white">
                    <h2 class="fw-bold mb-4">Área do Cliente <span style="color: #003366; text-shadow: 0px 0px 10px rgba(255,255,255,0.7);">PRO 👑</span></h2>
                    <p class="mb-5 opacity-75">Acesse sua conta para gerenciar suas propostas com performance máxima.</p>
                    
                    <div class="feature-item">
                        <div class="feature-icon">⚡</div>
                        <div class="feature-text">
                            <span class="feature-title">Edição em Tempo Real</span>
                            Crie e edite propostas instantaneamente. Tudo salvo na nuvem.
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">📲</div>
                        <div class="feature-text">
                            <span class="feature-title">Envio Agilizado</span>
                            Envie propostas para seus clientes via E-mail ou WhatsApp com um clique.
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">🛠️</div>
                        <div class="feature-text">
                            <span class="feature-title">Suporte Técnico</span>
                            Atendimento prioritário para resolver suas dúvidas.
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">🔔</div>
                        <div class="feature-text">
                            <span class="feature-title">Atualizações Contínuas</span>
                            Seja notificado sobre novas implementações e melhorias no sistema.
                        </div>
                    </div>

                    <div class="alert bg-warning-subtle text-dark border-0 mt-5 rounded-4 shadow-sm" role="alert">
                        <div class="d-flex">
                            <i class="fs-2 me-3">⚠️</i>
                            <div>
                                <strong class="text-uppercase mb-2 d-block" style="letter-spacing: 0.5px;">Atenção com sua senha!</strong>
                                <span class="d-block lh-lg" style="font-size: 0.9rem;">
                                    Jamais compartilhe sua senha. O acesso indevido compromete sua carteira de clientes, valores e histórico de negociações.
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Coluna de Login -->
        <div class="col-lg-4 col-md-8">
            <div class="card login-card bg-white border-0 h-100">
                <div class="login-header">
                    <h3 class="mb-1 fw-bold">Login Seguro</h3>
                    <p class="text-muted small">Insira suas credenciais</p>
                </div>
                <div class="card-body p-4">
                    
                    <?php if($erro): ?>
                        <div class="alert alert-danger text-center py-2"><?php echo $erro; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">USUÁRIO</label>
                            <input type="text" name="usuario" class="form-control form-control-lg bg-light" placeholder="Seu usuário" required autofocus>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">SENHA</label>
                            <input type="password" name="senha" class="form-control form-control-lg bg-light" placeholder="******" required>
                        </div>

                        <button type="submit" class="btn btn-custom w-100 btn-lg shadow-sm mb-3">
                            ENTRAR
                        </button>
                    </form>
                    
                    <div class="text-center border-top pt-3">
                        <a href="index.php" class="text-decoration-none text-muted small">&larr; Voltar ao Site</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>