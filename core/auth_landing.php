<?php
/**
 * core/auth_landing.php
 * Controlador de Autenticação da Landing Page
 */

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

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

// Se já está logado, redireciona para a página correta
if (isset($_SESSION['usuario_id'])) {
    if (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') {
        header('Location: Cli_demo.php');
    } else {
        header('Location: Cli_Pro.php');
    }
    exit;
}

$erro_login = '';
$modal_aberto = false;

// Lógica de Login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_SPECIAL_CHARS);
    $senha   = $_POST['senha'] ?? '';

    if (empty($usuario) || empty($senha)) {
        $erro_login = "Preencha usuário e senha.";
        $modal_aberto = true;
    } else {
        try {
            $conn = Database::getProd();
            
            $stmt = $conn->prepare("SELECT id_usuario, usuario, senha, nome_completo, tipo_perfil, validade_acesso FROM Usuarios WHERE usuario = ? LIMIT 1");
            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                $senha_valida = false;
                $precisa_migrar = false;

                if (password_verify($senha, $user['senha'])) {
                    $senha_valida = true;
                } elseif ($user['senha'] === $senha) {
                    $senha_valida = true;
                    $precisa_migrar = true;
                }

                if ($senha_valida) {
                    // Verifica Validade
                    $hoje = new DateTime();
                    $val = new DateTime($user['validade_acesso'] ? $user['validade_acesso'] : '2000-01-01');
                    
                    if ($hoje > $val && $user['tipo_perfil'] !== 'admin') {
                        $erro_login = "Sua assinatura venceu. Entre em contato.";
                        $modal_aberto = true;
                    } else {
                        // Migração de Senha (MD5 -> Bcrypt) se necessário
                        if ($precisa_migrar) {
                            $novo_hash = password_hash($senha, PASSWORD_DEFAULT);
                            $upd = $conn->prepare("UPDATE Usuarios SET senha = ?, ultimo_acesso = NOW() WHERE id_usuario = ?");
                            $upd->bind_param('si', $novo_hash, $user['id_usuario']);
                            $upd->execute();
                        } else {
                            $upd = $conn->prepare("UPDATE Usuarios SET ultimo_acesso = NOW() WHERE id_usuario = ?");
                            $upd->bind_param('i', $user['id_usuario']);
                            $upd->execute();
                        }

                        // Configura Sessão
                        $_SESSION['usuario_id']    = $user['id_usuario'];
                        $_SESSION['usuario_nome']  = $user['nome_completo'];
                        $_SESSION['perfil']        = $user['tipo_perfil'];
                        $_SESSION['ambiente']      = 'producao'; 
                        $_SESSION['origem_login']  = 'cliente';
                        
                        session_write_close();
                        header("Location: Cli_Pro.php");
                        exit;
                    }

                } else {
                    $erro_login = "Senha incorreta.";
                    $modal_aberto = true;
                }
            } else {
                $erro_login = "Usuário não encontrado.";
                $modal_aberto = true;
            }
        } catch (Exception $e) { 
            $erro_login = "Erro técnico no sistema."; 
            $modal_aberto = true;
        }
    }
}

return ['erro_login' => $erro_login, 'modal_aberto' => $modal_aberto];
