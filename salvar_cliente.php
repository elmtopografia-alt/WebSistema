<?php
// Nome do Arquivo: salvar_cliente.php
// Função: Processa cliente. Ajuste: Permite CPF/CNPJ vazio e duplicado.

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Segurança
if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$is_demo_flag = ($_SESSION['ambiente'] === 'demo') ? 1 : 0;
$conn = ($_SESSION['ambiente'] === 'demo') ? Database::getDemo() : Database::getProd();

// 2. Coleta de Dados
$acao       = $_POST['acao'];
$id_cliente = intval($_POST['id_cliente'] ?? 0);

$nome       = trim($_POST['nome_cliente']);
$empresa    = trim($_POST['empresa']);
// Se vier vazio, garantimos que seja NULL ou String Vazia para o banco aceitar
$doc        = trim($_POST['cnpj_cpf']); 
$email      = trim($_POST['email']);
$celular    = trim($_POST['celular']);
$telefone   = trim($_POST['telefone']);
$whatsapp   = !empty($celular) ? $celular : null; 

if (empty($nome)) {
    die("O nome do cliente é obrigatório.");
}

try {
    if ($acao === 'criar') {
        // --- INSERT (Sem checar duplicidade de CPF) ---
        // A única checagem recomendada seria por NOME, mas vamos deixar livre conforme solicitado.
        
        $sql = "INSERT INTO Clientes (
            nome_cliente, empresa, cnpj_cpf, email, telefone, celular, whatsapp, is_demo, id_criador
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssssii', 
            $nome, $empresa, $doc, $email, $telefone, $celular, $whatsapp, $is_demo_flag, $id_usuario
        );
        
        if ($stmt->execute()) {
            header("Location: meus_clientes.php?msg=sucesso");
        } else {
            // Se der erro de "Duplicate entry", é porque o Passo 1 (SQL no phpMyAdmin) não foi feito
            die("Erro ao salvar: Provavelmente o CPF já existe. " . $stmt->error);
        }

    } elseif ($acao === 'editar') {
        // --- UPDATE ---
        $sql = "UPDATE Clientes SET 
            nome_cliente = ?, empresa = ?, cnpj_cpf = ?, email = ?, 
            telefone = ?, celular = ?, whatsapp = ?
            WHERE id_cliente = ? AND id_criador = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssssii', 
            $nome, $empresa, $doc, $email, $telefone, $celular, $whatsapp, 
            $id_cliente, $id_usuario
        );
        
        if ($stmt->execute()) {
            header("Location: meus_clientes.php?msg=sucesso");
        } else {
            die("Erro ao atualizar: " . $stmt->error);
        }
    }

} catch (Exception $e) {
    echo "Erro técnico: " . $e->getMessage();
}