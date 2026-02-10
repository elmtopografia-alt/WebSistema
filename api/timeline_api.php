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
$conn = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') ? Database::getDemo() : Database::getProd();
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
                    DATE_FORMAT(h.data_interacao, '%H:%i') as hora_formatada
                FROM Historico_Interacoes h
                JOIN Propostas p ON h.id_proposta = p.id_proposta
                LEFT JOIN Usuarios u ON h.id_usuario = u.id
                WHERE h.id_proposta = ? AND p.id_criador = ?
                ORDER BY h.data_interacao DESC
                LIMIT 50
            ");
            $stmt->bind_param("ii", $id_proposta, $id_usuario);
            $stmt->execute();
            $result = $stmt->get_result();

            $timeline = [];
            while ($row = $result->fetch_assoc()) {
                $row['icone'] = getIconeTipo($row['tipo']);
                $row['cor'] = getCorTipo($row['tipo']);
                $row['titulo'] = formatarTitulo($row['tipo'], $row['conteudo']);
                $timeline[] = $row;
            }

            echo json_encode([
                'sucesso' => true,
                'timeline' => $timeline,
                'total' => count($timeline)
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
        'status_change' => 'arrows-left-right',
        'tarefa_criada' => 'calendar-plus',
        'tarefa_concluida' => 'check-circle',
        'email_enviado' => 'envelope-simple',
        'whatsapp_enviado' => 'whatsapp-logo',
        'nota_adicionada' => 'note',
        'arquivo_anexado' => 'paperclip',
        'reuniao_agendada' => 'users',
        'ligacao_realizada' => 'phone-call',
        'proposta_gerada' => 'file-text',
        'cobranca_enviada' => 'currency-dollar'
    ];
    return $icones[$tipo] ?? 'dot';
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
    
    // Se for status change, tenta extrair o novo status do conteúdo
    // Formato esperado: "Mudou de X para Y"
    if ($tipo === 'status_change') {
         if(preg_match('/para\s+(\w+)/i', $conteudo, $matches)) {
             $titulo .= ' → ' . ucfirst($matches[1]);
         }
    }
    
    return $titulo;
}
?>
