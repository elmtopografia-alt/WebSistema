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
$conn = Database::getProd();
$input = json_decode(file_get_contents('php://input'), true);
$acao = $input['acao'] ?? $_GET['acao'] ?? '';

// Helper: Registrar toda ação no histórico central
function registrarHistorico($conn, $id_proposta, $tipo, $conteudo, $id_usuario) {
    $stmt = $conn->prepare("
        INSERT INTO Historico_Interacoes 
        (id_proposta, tipo, conteudo, data_interacao, id_usuario) 
        VALUES (?, ?, ?, NOW(), ?)
    ");
    $stmt->bind_param("issi", $id_proposta, $tipo, $conteudo, $id_usuario);
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
            $agora = new DateTime();
            
            if ($data_agendada < $agora) {
                throw new Exception("Não é possível agendar tarefas no passado");
            }

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
            $msg = "Tarefa agendada: " . $input['tipo'] . " - " . $descricao . " para " . $input['data_agendada'];
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
            $filtro = $input['filtro'] ?? 'hoje'; // hoje, atrasadas, proximas, todas, concluidas
            $id_proposta = $input['id_proposta'] ?? null;
            
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

        // COMPLETAR TAREFA (com feedback e próxima ação sugerida)
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
                
                $resultado = $input['resultado'] ?? 'concluida'; // 'concluida', 'nao_atendeu', 'agendado_nova_data', 'recusou'
                $observacao = $input['observacao'] ?? '';
                
                $stmt->bind_param("ssii", $resultado, $observacao, $input['id_tarefa'], $id_usuario);
                $stmt->execute();

                // Registra no histórico
                $msg = "Tarefa concluída: " . $tarefa['tipo'] . ". Resultado: " . $resultado;
                if ($observacao) $msg .= " - " . $observacao;
                registrarHistorico($conn, $tarefa['id_proposta'], 'tarefa_concluida', $msg, $id_usuario);

                // Se resultado exige nova tarefa (ex: não atendeu), sugere criar nova
                $sugestao_proxima = null;
                if (in_array($resultado, ['nao_atendeu', 'recusou'])) {
                    $sugestao_proxima = [
                        'tipo' => 'ligacao',
                        'descricao' => 'Tentativa de contato - ' . ($resultado === 'recusou' ? 'Nova abordagem' : 'Segunda tentativa'),
                        'dias' => 2
                    ];
                }

                $conn->commit();

                echo json_encode([
                    'sucesso' => true,
                    'mensagem' => 'Tarefa concluída com sucesso',
                    'cliente' => $tarefa['nome_cliente'],
                    'sugestao_proxima' => $sugestao_proxima
                ]);

            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;

        // EXCLUIR TAREFA
        case 'excluir':
            if (empty($input['id_tarefa'])) {
                throw new Exception("ID da tarefa obrigatório");
            }

            $stmt = $conn->prepare("DELETE FROM Tarefas_CRM WHERE id_tarefa = ? AND id_usuario = ? AND status != 'concluida'");
            $stmt->bind_param("ii", $input['id_tarefa'], $id_usuario);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("Tarefa não encontrada ou já concluída (não pode ser excluída)");
            }

            echo json_encode(['sucesso' => true, 'mensagem' => 'Tarefa removida']);
            break;

        // ESTATÍSTICAS RÁPIDAS (para dashboard)
        case 'estatisticas':
            $stats = [];
            
            // Tarefas de hoje
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM Tarefas_CRM WHERE id_usuario = ? AND DATE(data_agendada) = CURDATE() AND status = 'pendente'");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $stats['hoje'] = $stmt->get_result()->fetch_assoc()['total'];

            // Atrasadas
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM Tarefas_CRM WHERE id_usuario = ? AND data_agendada < NOW() AND status = 'pendente'");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $stats['atrasadas'] = $stmt->get_result()->fetch_assoc()['total'];

            // Próximas 7 dias
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM Tarefas_CRM WHERE id_usuario = ? AND data_agendada BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY) AND status = 'pendente'");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $stats['proximas'] = $stmt->get_result()->fetch_assoc()['total'];

            // Taxa de conclusão (últimos 30 dias)
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(CASE WHEN status = 'concluida' THEN 1 END) as concluidas,
                    COUNT(*) as total
                FROM Tarefas_CRM 
                WHERE id_usuario = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stats['taxa_conclusao'] = $row['total'] > 0 ? round(($row['concluidas'] / $row['total']) * 100) : 0;

            echo json_encode(['sucesso' => true, 'estatisticas' => $stats]);
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
        'ligacao' => 'ph-phone-call',
        'email' => 'ph-envelope',
        'whatsapp' => 'ph-whatsapp-logo',
        'reuniao' => 'ph-users',
        'enviar_proposta' => 'ph-file-text',
        'cobranca' => 'ph-currency-dollar',
        'visita' => 'ph-map-pin'
    ];
    return $icones[$tipo] ?? 'ph-check-circle';
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