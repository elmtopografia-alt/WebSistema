<?php
// api/crm_controller.php
// Motor SGT - Controlador de Fluxo do CRM
// Responsável por: Validar sessão, Mover cards, Atualizar status real, Registrar logs.

header('Content-Type: application/json');
require_once '../db.php';
require_once '../session_validator.php';

// 1. Segurança: Apenas logados
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Acesso negado']);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$conn = isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo' ? Database::getDemo() : Database::getProd();

// 2. Recebe a requisição JSON
$input = json_decode(file_get_contents('php://input'), true);
$acao = $input['acao'] ?? '';

try {
    if ($acao === 'mover_card') {
        // Validação de Input
        $id_proposta = intval($input['id_proposta']);
        $nova_fase = $input['nova_fase']; // Ex: 'ENVIADA', 'FECHADA'

        // Mapeamento: Fase CRM -> Status Real do Banco
        // Isso garante que o Painel e o CRM falem a mesma língua
        $mapa_status = [
            'ELABORACAO' => 'Em Elaboração',
            'ENVIADA'    => 'Enviada',
            'NEGOCIACAO' => 'Enviada', // Negociação no CRM = Enviada no Banco (por enquanto) ou criar novo status? Vamos manter Enviada para compatibilidade
            'FECHADA'    => 'Aprovada',
            'PERDIDA'    => 'Cancelada',
            'CANCELADA'  => 'Cancelada'
        ];

        if (!array_key_exists($nova_fase, $mapa_status)) {
            throw new Exception("Fase inválida: $nova_fase");
        }

        $novo_status_banco = $mapa_status[$nova_fase];

        // 3. Execução Atômica
        $conn->begin_transaction();

        // Atualiza status e data de modificação
        // IMPORTANTE: WHERE id_criador garante isolamento
        $stmt = $conn->prepare("UPDATE Propostas SET status = ?, data_atualizacao = NOW() WHERE id_proposta = ? AND id_criador = ?");
        $stmt->bind_param('sii', $novo_status_banco, $id_proposta, $id_usuario);
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao atualizar banco: " . $stmt->error);
        }

        if ($stmt->affected_rows === 0) {
            throw new Exception("Proposta não encontrada ou você não tem permissão.");
        }

        // (Opcional) Log de auditoria poderia ser inserido aqui

        $conn->commit();
        echo json_encode(['sucesso' => true, 'novo_status' => $novo_status_banco]);

    } elseif ($acao === 'restaurar_proposta') {
        $id_proposta = intval($input['id_proposta'] ?? 0);
        $novo_status = $input['novo_status'] ?? 'Enviada';

        if (!$id_proposta) {
            throw new Exception("ID da proposta obrigatório");
        }

        $conn->begin_transaction();

        // Atualiza status
        $stmt = $conn->prepare("UPDATE Propostas SET status = ?, data_atualizacao = NOW() WHERE id_proposta = ? AND id_criador = ?");
        $stmt->bind_param("sii", $novo_status, $id_proposta, $id_usuario);
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao atualizar banco: " . $stmt->error);
        }

        if ($stmt->affected_rows === 0) {
            throw new Exception("Proposta não encontrada ou sem permissão");
        }

        // Histórico
        $stmtHist = $conn->prepare("INSERT INTO Historico_Interacoes (id_proposta, id_cliente, tipo, conteudo, id_usuario, data_interacao) SELECT id_proposta, id_cliente, 'restauracao', ?, ?, NOW() FROM Propostas WHERE id_proposta = ?");
        $msg = "Proposta restaurada de 'Cancelada' para '$novo_status'";
        $stmtHist->bind_param("sii", $msg, $id_usuario, $id_proposta);
        $stmtHist->execute();

        $conn->commit();
        echo json_encode(['sucesso' => true]);

    } else {
        throw new Exception("Ação desconhecida");
    }

} catch (Exception $e) {
    if (isset($conn)) $conn->rollback();
    http_response_code(400);
    echo json_encode(['erro' => $e->getMessage()]);
}
?>
