<?php
// Nome do Arquivo: salvar_cliente.php
// Função: Processa a inserção ou atualização no banco de dados.

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Segurança e Validação
if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Se tentar acessar direto pela URL ou não estiver logado, manda pro login
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$is_demo_flag = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') ? 1 : 0;
$conn = ($is_demo_flag === 1) ? Database::getDemo() : Database::getProd();

// 2. Coleta de Dados do Formulário
$acao       = $_POST['acao'] ?? '';
$id_cliente = intval($_POST['id_cliente'] ?? 0);

$nome       = trim($_POST['nome_cliente'] ?? '');
$empresa    = trim($_POST['empresa'] ?? '');
$doc        = trim($_POST['cnpj_cpf'] ?? '');
$email      = trim($_POST['email'] ?? '');
$celular    = trim($_POST['celular'] ?? '');
$telefone   = trim($_POST['telefone'] ?? '');
// Se não informar WhatsApp separado, assume o mesmo número do celular
$whatsapp   = !empty($celular) ? $celular : null; 

// Validação Básica
if (empty($nome)) {
    die("Erro: O nome do cliente é obrigatório. Volte e preencha.");
}

try {
    if ($acao === 'criar') {
        // --- INSERIR NOVO CLIENTE ---
        $sql = "INSERT INTO Clientes (
            nome_cliente, empresa, cnpj_cpf, email, telefone, celular, whatsapp, is_demo, id_criador
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssssii', 
            $nome, $empresa, $doc, $email, $telefone, $celular, $whatsapp, $is_demo_flag, $id_usuario
        );
        
        if ($stmt->execute()) {
            header("Location: meus_clientes.php?msg=sucesso");
            exit;
        } else {
            header("Location: meus_clientes.php?msg=erro");
            exit;
        }

    } elseif ($acao === 'editar') {
        // --- ATUALIZAR CLIENTE EXISTENTE ---
        // A cláusula AND id_criador garante que ninguém edite cliente de outro usuário
        $sql = "UPDATE Clientes SET 
            nome_cliente = ?, 
            empresa = ?, 
            cnpj_cpf = ?, 
            email = ?, 
            telefone = ?, 
            celular = ?, 
            whatsapp = ?
            WHERE id_cliente = ? AND id_criador = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssssii', 
            $nome, $empresa, $doc, $email, $telefone, $celular, $whatsapp, 
            $id_cliente, $id_usuario
        );
        
        if ($stmt->execute()) {
            header("Location: meus_clientes.php?msg=sucesso");
            exit;
        } else {
            header("Location: meus_clientes.php?msg=erro");
            exit;
        }
    } else {
        // Ação desconhecida
        header("Location: meus_clientes.php");
        exit;
    }

} catch (Exception $e) {
    // Log de erro (em produção não mostramos o erro técnico na tela, mas como é dev, mostramos)
    echo "Erro técnico no banco de dados: " . $e->getMessage();
}
?>