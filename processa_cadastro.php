<?php
// ARQUIVO: processa_cadastro.php
// FUNÇÃO: Criação de conta definitiva no ambiente de PRODUÇÃO

session_start();
require_once 'config.php';

// Exibe erros apenas se necessário para debug
ini_set('display_errors', 0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cadastrar.php");
    exit;
}

// 1. Conexão Explícita com Produção
// Como é um novo cadastro, forçamos a entrada no banco principal (Demanda)
$conn = new mysqli(DB_PROD_HOST, DB_PROD_USER, DB_PROD_PASS, DB_PROD_NAME);

if ($conn->connect_error) {
    $_SESSION['erro_cadastro'] = "Erro de conexão com o servidor. Tente mais tarde.";
    header("Location: cadastrar.php");
    exit;
}
$conn->set_charset("utf8mb4");

// 2. Sanitização
$nome = strip_tags(trim($_POST['nome'] ?? ''));
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$senha = trim($_POST['senha'] ?? '');

if (!$nome || !$email || !$senha) {
    $_SESSION['erro_cadastro'] = "Preencha todos os campos obrigatórios.";
    header("Location: cadastrar.php");
    exit;
}

// 3. Verifica Duplicidade
$stmt = $conn->prepare("SELECT id_usuario FROM Usuarios WHERE usuario = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['erro_cadastro'] = "Este e-mail já está cadastrado. Tente fazer login.";
    header("Location: cadastrar.php");
    exit;
}
$stmt->close();

// 4. Inserção do Usuário
// NOTA: Mantendo senha em texto plano conforme padrão atual do sistema (processa_login.php).
// Recomendação futura: Migrar todo o sistema para password_hash().
$setup = 0; // 0 = Envia para Setup ao logar, 1 = Pula setup
$ambiente = 'producao';

$sql_user = "INSERT INTO Usuarios (usuario, senha, nome_completo, setup_concluido, ambiente) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param('sssis', $email, $senha, $nome, $setup, $ambiente);

if ($stmt->execute()) {
    $id_novo_usuario = $conn->insert_id;
    
    // 5. Criação Automática de DadosEmpresa (Vital para não quebrar a Proposta)
    // Cria um registro vazio vinculado ao ID, para que o UPDATE no setup funcione
    $sql_emp = "INSERT INTO DadosEmpresa (id_criador, Empresa, CNPJ) VALUES (?, 'Minha Empresa', '')";
    $stmt_emp = $conn->prepare($sql_emp);
    $stmt_emp->bind_param('i', $id_novo_usuario);
    $stmt_emp->execute();
    
    // Sucesso
    $_SESSION['login_erro'] = null; // Limpa erros de login anteriores
    
    // Opcional: Auto-login
    // $_SESSION['usuario_id'] = $id_novo_usuario;
    // ...
    // Mas por segurança, vamos mandar ele logar:
    
    $_SESSION['sucesso_cadastro'] = "Conta criada com sucesso! Faça login para continuar.";
    header("Location: login_prod.php");
    exit;

} else {
    $_SESSION['erro_cadastro'] = "Erro ao registrar usuário: " . $conn->error;
    header("Location: cadastrar.php");
    exit;
}

$conn->close();
?>