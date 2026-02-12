<?php
// ajax_crm_action.php
require_once 'db.php';
require_once 'session_validator.php';

header('Content-Type: application/json');

$conn = Database::getProd();
$id_criador = $_SESSION['usuario_id'];

$acao = $_POST['acao'] ?? '';

if ($acao === 'registrar_touch') {
    $id_proposta = intval($_POST['id_proposta']);
    
    // Segurança: Verificar se pertence ao usuário
    $stmt = $conn->prepare("UPDATE Propostas SET data_ultimo_contato = NOW() WHERE id_proposta = ? AND id_criador = ?");
    $stmt->bind_param("ii", $id_proposta, $id_criador);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Interação registrada!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar.']);
    }
    exit;
}

if ($acao === 'update_status') {
    $id_proposta = intval($_POST['id_proposta']);
    $novo_status = $_POST['status'];
    
    // TODO: Implementar lógica mais complexa aqui se necessário (ex: atualizar valor_recebido se for Finalizada)
    
    $stmt = $conn->prepare("UPDATE Propostas SET status = ? WHERE id_proposta = ? AND id_criador = ?");
    $stmt->bind_param("sii", $novo_status, $id_proposta, $id_criador);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao mudar status.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
?>
