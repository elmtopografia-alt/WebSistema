<?php
// ARQUIVO: processa_demo_login.php
// FUNÇÃO: Login para usuários DEMO existentes (Autenticação Simples)

session_start();
require_once 'config.php';

// 1. Conexão Explícita com Banco DEMO
$conn = new mysqli(DB_DEMO_HOST, DB_DEMO_USER, DB_DEMO_PASS, DB_DEMO_NAME);

if ($conn->connect_error) {
    $_SESSION['login_erro_demo'] = "Erro de conexão momentânea.";
    header("Location: login_demo.php");
    exit;
}

$usuario = $_POST['usuario'] ?? '';
$senha   = $_POST['senha'] ?? '';

// 2. Busca Usuário
$stmt = $conn->prepare("SELECT id_usuario, nome_completo, senha, validade_acesso, setup_concluido FROM Usuarios WHERE usuario = ?");
$stmt->bind_param('s', $usuario);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $dados = $res->fetch_assoc();
    
    // 3. Verifica Senha (Comparação simples, pois no demo salvamos em texto plano por enquanto)
    if ($senha === $dados['senha']) {
        
        // 4. Verifica Validade
        $agora = new DateTime();
        $validade = new DateTime($dados['validade_acesso']);
        
        if ($agora > $validade) {
            $_SESSION['login_erro_demo'] = "Seu período de teste expirou. <a href='bloqueio_demo.php'>Saiba mais</a>";
            header("Location: login_demo.php");
            exit;
        }

        // 5. Sucesso - Configura Sessão
        $_SESSION['usuario_id'] = $dados['id_usuario'];
        $_SESSION['usuario_nome'] = $dados['nome_completo'];
        $_SESSION['setup_concluido'] = $dados['setup_concluido'];
        $_SESSION['ambiente'] = 'demo';
        $_SESSION['validade_demo'] = $dados['validade_acesso'];
        
        // Cookie para lembrar o e-mail
        setcookie('elm_demo_tracker', base64_encode($usuario), time() + (86400 * 30), "/");

        header("Location: index.php");
        exit;
    }
}

// Erro Genérico
$_SESSION['login_erro_demo'] = "E-mail ou senha incorretos.";
header("Location: login_demo.php");
exit;
?>