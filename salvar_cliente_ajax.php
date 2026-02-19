<?php
// Nome do Arquivo: salvar_cliente_ajax.php
// Função: Processa cadastro de cliente via AJAX (para modal em criar_proposta.php)
// Retorna JSON com sucesso ou erro

header('Content-Type: application/json');

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// 1. Segurança
if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Acesso não autorizado']);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$is_demo_flag = ($_SESSION['ambiente'] === 'demo') ? 1 : 0;
$conn = ($_SESSION['ambiente'] === 'demo') ? Database::getDemo() : Database::getProd();

// 2. Coleta de Dados
$nome     = trim($_POST['nome_cliente'] ?? '');
$empresa  = trim($_POST['empresa'] ?? '');
$doc      = trim($_POST['cnpj_cpf'] ?? '');
$email    = trim($_POST['email'] ?? '');
$celular  = trim($_POST['celular'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$whatsapp = !empty($celular) ? $celular : null;

// 3. Validação
if (empty($nome)) {
    echo json_encode(['success' => false, 'error' => 'O nome do cliente é obrigatório']);
    exit;
}

try {
    // INSERT do novo cliente
    $sql = "INSERT INTO Clientes (
        nome_cliente, empresa, cnpj_cpf, email, telefone, celular, whatsapp, is_demo, id_criador
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssssii', 
        $nome, $empresa, $doc, $email, $telefone, $celular, $whatsapp, $is_demo_flag, $id_usuario
    );
    
    if ($stmt->execute()) {
        $novo_id = $conn->insert_id;
        
        echo json_encode([
            'success'  => true,
            'id'       => $novo_id,
            'nome'     => $nome,
            'empresa'  => $empresa,
            'email'    => $email,
            'celular'  => $celular,
            'telefone' => $telefone
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro ao salvar: ' . $stmt->error]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erro técnico: ' . $e->getMessage()]);
}
