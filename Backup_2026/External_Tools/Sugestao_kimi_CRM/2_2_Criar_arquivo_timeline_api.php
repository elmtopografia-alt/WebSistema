<?php
// api/timeline_api.php - Timeline 360° do Cliente/Proposta

header('Content-Type: application/json');
require_once '../db.php';
require_once '../session_validator.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autorizado']);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$conn = Database::getProd();
$input = json_decode(file_get_contents('php://input'), true);

$acao = $input['acao'] ?? $_GET['acao'] ?? '';

try {
    switch ($acao) {
        case 'timeline_proposta':
            $id_proposta = intval($_GET['id_proposta'] ?? 0);
            if (!$id_proposta) throw new Exception("ID da proposta obrigatório");

            // Busca timeline completa da proposta
            $stmt = $conn->prepare("
                SELECT 
                    h.*,
                    u.nome as nome_usuario,
                    DATE_FORMAT(h.data_interacao, '%d/%m/%Y %H:%i') as data_formatada,
                    DATE_FORMAT(h.data_interacao, '%H:%i') as hora_formatada,
                    CASE 
                        WHEN h.data_interacao >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 'hoje'
                        WHEN h.data_interacao >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 'esta_semana'
                        ELSE 'antigo'
                    END as periodo
                FROM Historico_Interacoes h
                LEFT JOIN Usuarios u ON h.id_usuario = u.id
                WHERE h.id_proposta = ? 
                ORDER BY h.data_interacao DESC
                LIMIT 50
            ");
            $stmt->bind_param("i", $id_proposta);
            $stmt->execute();
            $result = $stmt->get_result();

            $timeline = [];
            while ($row = $result->fetch_assoc()) {
                $row['icone'] = getIconeTipo($row['tipo']);
                $row['cor'] = getCorTipo($row['tipo']);
                $row['titulo'] = formatarTitulo($row['tipo'], $row['conteudo']);
                $timeline[] = $row;
            }

            // Busca também dados da proposta para contexto
            $stmt = $conn->prepare("
                SELECT p.*, c.nome_cliente, c.whatsapp, c.email, c.empresa
                FROM Propostas p
                JOIN Clientes c ON p.id_cliente = c.id_cliente
                WHERE p.id_proposta = ? AND p.id_criador = ?
            ");
            $stmt->bind_param("ii", $id_proposta, $id_usuario);
            $stmt->execute();
            $proposta = $stmt->get_result()->fetch_assoc();

            if (!$proposta) {
                throw new Exception("Proposta não encontrada ou sem permissão");
            }

            echo json_encode([
                'sucesso' => true,
                'proposta' => $proposta,
                'timeline' => $timeline,
                'total' => count($timeline)
            ]);
            break;

        case 'timeline_cliente':
            // Timeline agregada de todas as propostas do cliente
            $id_cliente = intval($_GET['id_cliente'] ?? 0);
            
            $stmt = $conn->prepare("
                SELECT 
                    h.*,
                    p.id_proposta,
                    p.status as status_proposta,
                    p.valor_total,
                    u.nome as nome_usuario
                FROM Historico_Interacoes h
                JOIN Propostas p ON h.id_proposta = p.id_proposta
                LEFT JOIN Usuarios u ON h.id_usuario = u.id
                WHERE h.id_cliente = ? AND p.id_criador = ?
                ORDER BY h.data_interacao DESC
                LIMIT 100
            ");
            $stmt->bind_param("ii", $id_cliente, $id_usuario);
            $stmt->execute();
            
            $timeline = [];
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $row['icone'] = getIconeTipo($row['tipo']);
                $row['cor'] = getCorTipo($row['tipo']);
                $timeline[] = $row;
            }

            // Resumo do cliente
            $stmt = $conn->prepare("
                SELECT c.*, 
                       COUNT(DISTINCT p.id_proposta) as total_propostas,
                       SUM(CASE WHEN p.status = 'Aprovada' THEN 1 ELSE 0 END) as propostas_ganhas,
                       SUM(CASE WHEN p.status = 'Aprovada' THEN p.valor_total ELSE 0 END) as valor_total_vendido,
                       MAX(p.data_criacao) as ultima_proposta
                FROM Clientes c
                LEFT JOIN Propostas p ON c.id_cliente = p.id_cliente AND p.id_criador = ?
                WHERE c.id_cliente = ? AND c.id_criador = ?
                GROUP BY c.id_cliente
            ");
            $stmt->bind_param("iii", $id_usuario, $id_cliente, $id_usuario);
            $stmt->execute();
            $cliente = $stmt->get_result()->fetch_assoc();

            echo json_encode([
                'sucesso' => true,
                'cliente' => $cliente,
                'timeline' => $timeline,
                'resumo' => [
                    'taxa_conversao' => $cliente['total_propostas'] > 0 
                        ? round(($cliente['propostas_ganhas'] / $cliente['total_propostas']) * 100, 1) 
                        : 0,
                    'ticket_medio' => $cliente['propostas_ganhas'] > 0 
                        ? round($cliente['valor_total_vendido'] / $cliente['propostas_ganhas'], 2) 
                        : 0
                ]
            ]);
            break;

        case 'adicionar_nota':
            // Adicionar nota manual à timeline
            $id_proposta = intval($input['id_proposta'] ?? 0);
            $conteudo = trim($input['conteudo'] ?? '');
            
            if (!$id_proposta || empty($conteudo)) {
                throw new Exception("Proposta e conteúdo são obrigatórios");
            }

            // Busca id_cliente da proposta
            $stmt = $conn->prepare("SELECT id_cliente FROM Propostas WHERE id_proposta = ? AND id_criador = ?");
            $stmt->bind_param("ii", $id_proposta, $id_usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Proposta não encontrada");
            }
            
            $id_cliente = $result->fetch_assoc()['id_cliente'];

            $stmt = $conn->prepare("
                INSERT INTO Historico_Interacoes 
                (id_proposta, id_cliente, tipo, conteudo, canal, id_usuario) 
                VALUES (?, ?, 'nota_adicionada', ?, 'sistema', ?)
            ");
            $stmt->bind_param("iisi", $id_proposta, $id_cliente, $conteudo, $id_usuario);
            $stmt->execute();

            echo json_encode([
                'sucesso' => true,
                'id_historico' => $conn->insert_id,
                'mensagem' => 'Nota adicionada com sucesso'
            ]);
            break;

        default:
            throw new Exception("Ação inválida");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['erro' => $e->getMessage()]);
}

function getIconeTipo($tipo) {
    $icones = [
        'status_change' => 'ph-arrows-left-right',
        'tarefa_criada' => 'ph-calendar-plus',
        'tarefa_concluida' => 'ph-check-circle',
        'email_enviado' => 'ph-envelope-simple',
        'whatsapp_enviado' => 'ph-whatsapp-logo',
        'nota_adicionada' => 'ph-note',
        'arquivo_anexado' => 'ph-paperclip',
        'reuniao_agendada' => 'ph-users',
        'ligacao_realizada' => 'ph-phone-call',
        'proposta_gerada' => 'ph-file-text',
        'cobranca_enviada' => 'ph-currency-dollar'
    ];
    return $icones[$tipo] ?? 'ph-dot';
}

function getCorTipo($tipo) {
    $cores = [
        'status_change' => 'blue',
        'tarefa_criada' => 'purple',
        'tarefa_concluida' => 'green',
        'email_enviado' => 'indigo',
        'whatsapp_enviado' => 'green',
        'nota_adicionada' => 'yellow',
        'arquivo_anexado' => 'gray',
        'reuniao_agendada' => 'pink',
        'ligacao_realizada' => 'cyan',
        'proposta_gerada' => 'orange',
        'cobranca_enviada' => 'red'
    ];
    return $cores[$tipo] ?? 'gray';
}

function formatarTitulo($tipo, $conteudo) {
    $titulos = [
        'status_change' => 'Status alterado',
        'tarefa_criada' => 'Tarefa agendada',
        'tarefa_concluida' => 'Tarefa concluída',
        'email_enviado' => 'E-mail enviado',
        'whatsapp_enviado' => 'WhatsApp enviado',
        'nota_adicionada' => 'Nota adicionada',
        'arquivo_anexado' => 'Arquivo anexado',
        'reuniao_agendada' => 'Reunião agendada',
        'ligacao_realizada' => 'Ligação realizada',
        'proposta_gerada' => 'Proposta gerada',
        'cobranca_enviada' => 'Cobrança enviada'
    ];
    
    $titulo = $titulos[$tipo] ?? 'Atividade';
    
    // Se for status change, extrai o novo status do conteúdo
    if ($tipo === 'status_change' && preg_match('/para:?\s*(\w+)/i', $conteudo, $matches)) {
        $titulo .= ' → ' . $matches[1];
    }
    
    return $titulo;
}
?>