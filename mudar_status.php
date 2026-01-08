<?php
// Inicio: mudar_status.php
// Função: Atualiza o status da proposta.
// Melhoria: Suporta requisições AJAX para atualização de gráficos em tempo real sem reload.

session_start();
require_once 'config.php';
require_once 'db.php';

// Resposta padrão JSON (para AJAX)
$response = ['success' => false, 'msg' => 'Acesso negado'];

// 1. Verificação de Segurança
if (isset($_SESSION['usuario_id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_usuario = $_SESSION['usuario_id'];
    $ambiente = $_SESSION['ambiente'] ?? 'producao';
    $conn = ($ambiente === 'demo') ? Database::getDemo() : Database::getProd();

    // 2. Coleta e Sanitização
    $id_proposta = intval($_POST['id_proposta'] ?? 0);
    $novo_status = trim($_POST['novo_status'] ?? '');
    
    // Verifica se é uma chamada AJAX (para não redirecionar)
    $is_ajax = isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1';

    // Lista estrita de status permitidos
    $status_validos = ['Em elaboração', 'Enviada', 'Aprovada', 'Cancelada', 'Concluída'];

    if ($id_proposta > 0 && in_array($novo_status, $status_validos)) {
        
        try {
            // 3. Atualização no Banco
            $sql = "UPDATE Propostas SET status = ? WHERE id_proposta = ? AND id_criador = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sii', $novo_status, $id_proposta, $id_usuario);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['msg'] = "Status alterado para: $novo_status";
                
                // Se NÃO for AJAX (uso antigo/fallback), redireciona
                if (!$is_ajax) {
                    header("Location: painel.php?msg=status_atualizado");
                    exit;
                }
            } else {
                $response['msg'] = "Erro ao atualizar banco.";
                if (!$is_ajax) { header("Location: painel.php?msg=erro"); exit; }
            }

        } catch (Exception $e) {
            $response['msg'] = "Erro técnico: " . $e->getMessage();
            if (!$is_ajax) { header("Location: painel.php?msg=erro"); exit; }
        }
        
    } else {
        $response['msg'] = "Dados inválidos.";
        if (!$is_ajax) { header("Location: painel.php?msg=erro"); exit; }
    }
}

// Retorna JSON se for AJAX
header('Content-Type: application/json');
echo json_encode($response);
exit;
// Fim: mudar_status.php
?>