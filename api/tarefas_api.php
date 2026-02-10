<?php
// api/tarefas_api.php - SGT CRM - Gestão Inteligente de Tarefas
// Responsável: CRUD tarefas, lembretes automáticos, notificações

header('Content-Type: application/json');
require_once '../db.php';
require_once '../session_validator.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$conn = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') ? Database::getDemo() : Database::getProd();
$input = json_decode(file_get_contents('php://input'), true);
$acao = $input['acao'] ?? $_GET['acao'] ?? '';

// Helper: Registrar toda ação no histórico central
function registrarHistorico($conn, $id_proposta, $tipo, $conteudo, $id_usuario) {
    if (!$id_proposta) return false;
    
    // Busca id_cliente da proposta para manter integridade
    $stmtCli = $conn->prepare("SELECT id_cliente FROM Propostas WHERE id_proposta = ?");
    $stmtCli->bind_param("i", $id_proposta);
    $stmtCli->execute();
    $resCli = $stmtCli->get_result()->fetch_assoc();
    $id_cliente = $resCli['id_cliente'] ?? 0;

    $stmt = $conn->prepare("
        INSERT INTO Historico_Interacoes 
        (id_proposta, id_cliente, tipo, conteudo, data_interacao, id_usuario) 
        VALUES (?, ?, ?, ?, NOW(), ?)
    ");
    $stmt->bind_param("iissi", $id_proposta, $id_cliente, $tipo, $conteudo, $id_usuario);
    return $stmt->execute();
}

// Helper: Buscar dados da proposta para notificações
function getPropostaInfo($conn, $id_proposta) {
    $stmt = $conn->prepare("
        SELECT p.*, c.nome_cliente, c.whatsapp, c.email 
        FROM Propostas p 
        JOIN Clientes c ON p.id_cliente = c.id_cliente 
        WHERE p.id_proposta = ?
    ");
    $stmt->bind_param("i", $id_proposta);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

try {
    switch ($acao) {
        // CRIAR TAREFA (com validação inteligente)
        case 'criar':
            if (empty($input['id_proposta']) || empty($input['tipo']) || empty($input['data_agendada'])) {
                throw new Exception("Campos obrigatórios: id_proposta, tipo, data_agendada");
            }

            // Validações de negócio
            $data_agendada = new DateTime($input['data_agendada']);
            
            // Permite criar tarefa levemente no passado (ex: logar ligação que acabou de fazer)
            // mas avisa se for muito antigo? Não, deixa flexível por enquanto.
            
            $stmt = $conn->prepare("
                INSERT INTO Tarefas_CRM 
                (id_proposta, id_usuario, tipo, descricao, data_agendada, prioridade, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'pendente', NOW())
            ");
            
            $prioridade = $input['prioridade'] ?? 'media';
            $descricao = $input['descricao'] ?? 'Follow-up automático';
            
            $stmt->bind_param("iissss", 
                $input['id_proposta'],
                $id_usuario,
                $input['tipo'], // 'ligacao', 'email', 'whatsapp', 'reuniao', 'enviar_proposta', 'cobranca'
                $descricao,
                $input['data_agendada'],
                $prioridade
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao criar tarefa: " . $stmt->error);
            }

            $id_tarefa = $conn->insert_id;
            
            // Registra no histórico
            $info = getPropostaInfo($conn, $input['id_proposta']);
            $msg = "Agendado: " . ucfirst($input['tipo']) . " - " . $descricao . " (" . date('d/m H:i', strtotime($input['data_agendada'])) . ")";
            registrarHistorico($conn, $input['id_proposta'], 'tarefa_criada', $msg, $id_usuario);

            echo json_encode([
                'sucesso' => true, 
                'id_tarefa' => $id_tarefa,
                'mensagem' => 'Tarefa criada com sucesso',
                'cliente' => $info['nome_cliente'] ?? 'Desconhecido'
            ]);
            break;

        // LISTAR TAREFAS (com filtros inteligentes)
        case 'listar':
            $filtro = $input['filtro'] ?? $_GET['filtro'] ?? 'hoje'; // hoje, atrasadas, proximas, todas, concluidas
            $id_proposta = $input['id_proposta'] ?? $_GET['id_proposta'] ?? null;
            
            $sql = "
                SELECT 
                    t.*,
                    p.id_cliente,
                    p.valor_total,
                    c.nome_cliente,
                    c.whatsapp,
                    DATEDIFF(t.data_agendada, NOW()) as dias_restantes,
                    CASE 
                        WHEN t.data_agendada < NOW() AND t.status = 'pendente' THEN 'atrasada'
                        WHEN DATEDIFF(t.data_agendada, NOW()) = 0 THEN 'hoje'
                        WHEN DATEDIFF(t.data_agendada, NOW()) <= 3 THEN 'urgente'
                        ELSE 'futura'
                    END as urgencia
                FROM Tarefas_CRM t
                JOIN Propostas p ON t.id_proposta = p.id_proposta
                JOIN Clientes c ON p.id_cliente = c.id_cliente
                WHERE t.id_usuario = ?
            ";
            
            $params = [$id_usuario];
            $types = "i";

            if ($id_proposta) {
                $sql .= " AND t.id_proposta = ?";
                $params[] = $id_proposta;
                $types .= "i";
            }

            switch ($filtro) {
                case 'hoje':
                    $sql .= " AND DATE(t.data_agendada) = CURDATE() AND t.status = 'pendente'";
                    break;
                case 'atrasadas':
                    $sql .= " AND t.data_agendada < NOW() AND t.status = 'pendente'";
                    break;
                case 'proximas':
                    $sql .= " AND t.data_agendada BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY) AND t.status = 'pendente'";
                    break;
                case 'concluidas':
                    $sql .= " AND t.status = 'concluida'";
                    break;
                case 'pendentes':
                    $sql .= " AND t.status = 'pendente'";
                    break;
            }

            $sql .= " ORDER BY 
                CASE t.prioridade WHEN 'alta' THEN 1 WHEN 'media' THEN 2 ELSE 3 END,
                t.data_agendada ASC";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $tarefas = [];
            while ($row = $result->fetch_assoc()) {
                // Formatações amigáveis
                $data = new DateTime($row['data_agendada']);
                $row['data_formatada'] = $data->format('d/m/Y H:i');
                $row['hora'] = $data->format('H:i');
                $row['icone'] = getIconeTarefa($row['tipo']);
                $row['cor_urgencia'] = getCorUrgencia($row['urgencia']);
                $tarefas[] = $row;
            }

            echo json_encode([
                'sucesso' => true,
                'filtro' => $filtro,
                'total' => count($tarefas),
                'tarefas' => $tarefas
            ]);
            break;

        // COMPLETAR TAREFA (com feedback)
        case 'completar':
            if (empty($input['id_tarefa'])) {
                throw new Exception("ID da tarefa obrigatório");
            }

            $conn->begin_transaction();

            try {
                // Busca tarefa antes de completar
                $stmt = $conn->prepare("
                    SELECT t.*, p.id_cliente, c.nome_cliente 
                    FROM Tarefas_CRM t
                    JOIN Propostas p ON t.id_proposta = p.id_proposta
                    JOIN Clientes c ON p.id_cliente = c.id_cliente
                    WHERE t.id_tarefa = ? AND t.id_usuario = ?
                ");
                $stmt->bind_param("ii", $input['id_tarefa'], $id_usuario);
                $stmt->execute();
                $tarefa = $stmt->get_result()->fetch_assoc();

                if (!$tarefa) {
                    throw new Exception("Tarefa não encontrada ou sem permissão");
                }

                // Atualiza status
                $stmt = $conn->prepare("
                    UPDATE Tarefas_CRM 
                    SET status = 'concluida', 
                        data_conclusao = NOW(),
                        resultado = ?,
                        observacao = ?
                    WHERE id_tarefa = ? AND id_usuario = ?
                ");
                
                $resultado = $input['resultado'] ?? 'concluida'; 
                $observacao = $input['observacao'] ?? '';
                
                $stmt->bind_param("ssii", $resultado, $observacao, $input['id_tarefa'], $id_usuario);
                $stmt->execute();

                // Registra no histórico
                $msg = "Tarefa concluída: " . ucfirst($tarefa['tipo']) . ". Resultado: " . ucfirst(str_replace('_', ' ', $resultado));
                if ($observacao) $msg .= " - " . $observacao;
                registrarHistorico($conn, $tarefa['id_proposta'], 'tarefa_concluida', $msg, $id_usuario);

                $conn->commit();

                echo json_encode([
                    'sucesso' => true,
                    'mensagem' => 'Tarefa concluída com sucesso'
                ]);

            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;
            
        case 'excluir':
             if (empty($input['id_tarefa'])) {
                throw new Exception("ID da tarefa obrigatório");
            }
            $stmt = $conn->prepare("DELETE FROM Tarefas_CRM WHERE id_tarefa = ? AND id_usuario = ?");
            $stmt->bind_param("ii", $input['id_tarefa'], $id_usuario);
            $stmt->execute();
            echo json_encode(['sucesso' => true]);
            break;

        default:
            throw new Exception("Ação não reconhecida: " . $acao);
    }

} catch (Exception $e) {
    if (isset($conn) && $conn->connect_errno === 0) {
        $conn->rollback();
    }
    http_response_code(400);
    echo json_encode(['erro' => $e->getMessage()]);
}

// Helpers de formatação
function getIconeTarefa($tipo) {
    $icones = [
        'ligacao' => 'phone-call',
        'email' => 'envelope',
        'whatsapp' => 'whatsapp-logo',
        'reuniao' => 'users',
        'enviar_proposta' => 'file-text',
        'cobranca' => 'currency-dollar',
        'visita' => 'map-pin'
    ];
    return $icones[$tipo] ?? 'check-circle';
}

function getCorUrgencia($urgencia) {
    $cores = [
        'atrasada' => 'red',
        'hoje' => 'orange',
        'urgente' => 'yellow',
        'futura' => 'blue'
    ];
    return $cores[$urgencia] ?? 'gray';
}
?>
