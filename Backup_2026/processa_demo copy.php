<?php
// ARQUIVO: processa_demo.php
// VERSÃO: CORREÇÃO DE SESSÃO (Garante exibição do Modal)

session_start();
require_once 'config.php';

// Exibe erros apenas para debug
ini_set('display_errors', 0); 

// 1. Conexão com Banco DEMO
$conn = new mysqli(DB_DEMO_HOST, DB_DEMO_USER, DB_DEMO_PASS, DB_DEMO_NAME);

if ($conn->connect_error) {
    die("Erro de conexão (Demo): " . $conn->connect_error);
}

$email_original = $_POST['email_demo'] ?? '';
if (!filter_var($email_original, FILTER_VALIDATE_EMAIL)) {
    die("E-mail inválido. Volte e tente novamente.");
}

// Gera nova senha sempre que iniciar um teste novo
$nova_senha = rand(100000, 999999);

// 2. Busca Usuário Existente
$stmt = $conn->prepare("SELECT id_usuario, nome_completo, validade_acesso FROM Usuarios WHERE usuario = ?");
$stmt->bind_param('s', $email_original);
$stmt->execute();
$res = $stmt->get_result();

$id_usuario = null;
$nome = "";
$validade = "";

if ($res->num_rows > 0) {
    // RECUPERAÇÃO
    $dados = $res->fetch_assoc();
    $id_usuario = $dados['id_usuario'];
    $validade = $dados['validade_acesso'];
    $nome = $dados['nome_completo'];

    // Atualiza a senha para a nova gerada
    $upd = $conn->prepare("UPDATE Usuarios SET senha = ? WHERE id_usuario = ?");
    $upd->bind_param('si', $nova_senha, $id_usuario);
    $upd->execute();
} else {
    // NOVO USUÁRIO
    $partes = explode('@', $email_original);
    $nome = "Visitante (" . $partes[0] . ")";
    $validade = date('Y-m-d H:i:s', strtotime('+5 days'));
    
    $ins = $conn->prepare("INSERT INTO Usuarios (usuario, senha, nome_completo, setup_concluido, ambiente, validade_acesso) VALUES (?, ?, ?, 1, 'demo', ?)");
    $ins->bind_param('ssss', $email_original, $nova_senha, $nome, $validade);
    
    if ($ins->execute()) {
        $id_usuario = $conn->insert_id;
        
        // Cria empresa fictícia para não travar
        $sql_emp = "INSERT INTO DadosEmpresa (id_criador, Empresa, CNPJ, Cidade) VALUES (?, 'Minha Empresa Demo', '00.000.000/0001-00', 'Cidade Modelo')";
        $stmt_emp = $conn->prepare($sql_emp);
        $stmt_emp->bind_param('i', $id_usuario);
        $stmt_emp->execute();
    } else {
        die("Erro ao criar usuário: " . $conn->error);
    }
}

// 3. Configura Sessão
$_SESSION['usuario_id'] = $id_usuario;
$_SESSION['usuario_nome'] = $nome;
$_SESSION['setup_concluido'] = 1;
$_SESSION['ambiente'] = 'demo';
$_SESSION['validade_demo'] = $validade;

// --- GATILHO DO MODAL ---
$_SESSION['show_credentials'] = true;     // Ativa o modal no index.php
$_SESSION['temp_user'] = $email_original; // Passa o usuário
$_SESSION['temp_pass'] = $nova_senha;     // Passa a senha
// ------------------------

// Cookie para lembrar o e-mail no login_demo.php
setcookie('elm_demo_tracker', base64_encode($email_original), time() + (86400 * 30), "/");

$conn->close();

// Redireciona
header("Location: index.php");
exit;
?>