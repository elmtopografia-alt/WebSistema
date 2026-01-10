<?php
// ajax_atualizar_status.php
require_once 'db.php';

// Inicia sessão se não houver (para pegar o id do usuário logado por segurança)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Verifica se os dados chegaram via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_proposta = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $novo_status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    
    // IMPORTANTE: Pegue o ID do criador da sessão para garantir que ninguém altere proposta de outro usuário
    $id_criador = $_SESSION['id_usuario'] ?? null; // Ajuste 'id_usuario' conforme o nome da sua variável de sessão

    if ($id_proposta && $novo_status) {
        try {
            // Usa sua classe Database configurada anteriormente
            $conn = Database::getProd(); 

            // Atualiza o status APENAS se a proposta pertencer ao usuário logado (segurança SaaS)
            // Se você não usar login ainda, remova a parte "AND id_criador = ?"
            $sql = "UPDATE Propostas SET status = ? WHERE id_proposta = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $novo_status, $id_proposta);
            
            if ($stmt->execute()) {
                echo json_encode(['sucesso' => true, 'mensagem' => 'Status atualizado!']);
            } else {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar banco.']);
            }
        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Dados incompletos.']);
    }
}
?>