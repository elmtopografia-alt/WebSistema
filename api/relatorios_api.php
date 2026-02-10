<?php
// api/relatorios_api.php - API de Relatórios SGT CRM

header('Content-Type: application/json; charset=utf-8');

// Carrega db.php
$possiveis_caminhos = ['../db.php', '../../db.php', __DIR__ . '/../db.php', __DIR__ . '/../../db.php'];
$db_carregado = false;
foreach ($possiveis_caminhos as $caminho) {
    if (file_exists($caminho)) {
        require_once $caminho;
        $db_carregado = true;
        break;
    }
}

if (!$db_carregado) {
    echo json_encode(['sucesso' => false, 'erro' => 'db.php não encontrado']);
    exit;
}

if (!class_exists('Database')) {
    echo json_encode(['sucesso' => false, 'erro' => 'Classe Database não encontrada']);
    exit;
}

// Sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

if (empty($id_usuario)) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID usuário vazio']);
    exit;
}

// Conexão COM TRY-CATCH CORRETO
try {
    if (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') {
        $conn = Database::getDemo();
    } else {
        $conn = Database::getProd();
    }
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro conexão: ' . $e->getMessage()]);
    exit;
}

// Parâmetros
$acao = isset($_GET['acao']) ? $_GET['acao'] : (isset($_POST['acao']) ? $_POST['acao'] : '');
$periodo = isset($_GET['periodo']) && !empty($_GET['periodo']) ? $_GET['periodo'] : 'mes';

switch($periodo) {
    case 'hoje': $dias = 1; break;
    case 'semana': $dias = 7; break;
    case 'trimestre': $dias = 90; break;
    case 'mes': 
    default: $dias = 30; break;
}

$data_inicio = date('Y-m-d H:i:s', strtotime("-$dias days"));

// Roteamento
switch ($acao) {
    case 'dashboard':
        echo json_encode(gerarDashboard($conn, $id_usuario, $data_inicio, $dias));
        break;
    case 'comparativo':
        echo json_encode(gerarComparativo($conn, $id_usuario, $dias));
        break;
    default:
        echo json_encode(['sucesso' => false, 'erro' => 'Ação inválida: ' . $acao]);
}

// ============================================================
// FUNÇÕES
// ============================================================

function gerarDashboard($conn, $id_usuario, $data_inicio, $dias) {
    $resultado = [
        'sucesso' => true,
        'kpis' => [],
        'funil' => [],
        'evolucao' => [],
        'top_clientes' => [],
        'produtividade' => []
    ];
    
    try {
        if (empty($id_usuario) || empty($data_inicio)) {
            throw new Exception("Parâmetros inválidos");
        }

        // 1. KPIs - Propostas Criadas
        $sql = "SELECT COUNT(*) as total, COALESCE(SUM(valor_final_proposta), 0) as valor_total 
                FROM Propostas 
                WHERE id_criador = ? AND data_criacao >= ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare erro: " . $conn->error);
        
        $stmt->bind_param("is", $id_usuario, $data_inicio);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        
        $resultado['kpis']['propostas_criadas'] = [
            'total' => (int)$row['total'],
            'valor_total' => (float)$row['valor_total']
        ];

        // 2. KPIs - Conversão
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status IN ('Aprovada', 'Finalizada', 'Fechada') THEN 1 ELSE 0 END) as aprovadas,
                    SUM(CASE WHEN status IN ('Cancelada', 'Perdida', 'Arquivada') THEN 1 ELSE 0 END) as perdidas,
                    SUM(CASE WHEN status IN ('Aprovada', 'Finalizada', 'Fechada') THEN valor_final_proposta ELSE 0 END) as valor_ganho
                FROM Propostas 
                WHERE id_criador = ? AND data_criacao >= ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $id_usuario, $data_inicio);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        
        $total = (int)$row['total'];
        $aprovadas = (int)$row['aprovadas'];
        
        $resultado['kpis']['conversao'] = [
            'total' => $total,
            'aprovadas' => $aprovadas,
            'perdidas' => (int)$row['perdidas'],
            'taxa' => $total > 0 ? round(($aprovadas / $total) * 100, 1) : 0,
            'valor_ganho' => (float)$row['valor_ganho']
        ];

        // 3. Tempo Médio
        $sql = "SELECT AVG(DATEDIFF(data_atualizacao, data_criacao)) as media_dias
                FROM Propostas 
                WHERE id_criador = ? AND status IN ('Aprovada', 'Finalizada', 'Fechada') AND data_criacao >= ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $id_usuario, $data_inicio);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $resultado['kpis']['tempo_conversao'] = (float)($row['media_dias'] ?? 0);

        // 4. Funil
        $sql = "SELECT status, COUNT(*) as quantidade, COALESCE(SUM(valor_final_proposta), 0) as valor
                FROM Propostas 
                WHERE id_criador = ? AND data_criacao >= ?
                GROUP BY status
                ORDER BY quantidade DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $id_usuario, $data_inicio);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $funil = [];
        while ($row = $res->fetch_assoc()) {
            $funil[] = [
                'status' => $row['status'],
                'quantidade' => (int)$row['quantidade'],
                'valor' => (float)$row['valor']
            ];
        }
        $resultado['funil'] = $funil;

        // 5. Evolução
        $sql = "SELECT DATE(data_criacao) as data, COUNT(*) as propostas,
                    SUM(CASE WHEN status IN ('Aprovada', 'Finalizada', 'Fechada') THEN 1 ELSE 0 END) as aprovadas
                FROM Propostas 
                WHERE id_criador = ? AND data_criacao >= ?
                GROUP BY DATE(data_criacao)
                ORDER BY data ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $id_usuario, $data_inicio);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $evolucao = [];
        while ($row = $res->fetch_assoc()) {
            $evolucao[] = [
                'data' => $row['data'],
                'propostas' => (int)$row['propostas'],
                'aprovadas' => (int)$row['aprovadas']
            ];
        }
        $resultado['evolucao'] = $evolucao;

        // 6. Top Clientes
        $sql = "SELECT c.nome_cliente, c.empresa, COUNT(p.id_proposta) as total_propostas,
                    SUM(CASE WHEN p.status IN ('Aprovada', 'Finalizada', 'Fechada') THEN p.valor_final_proposta ELSE 0 END) as valor_comprado
                FROM Clientes c
                JOIN Propostas p ON c.id_cliente = p.id_cliente
                WHERE p.id_criador = ? AND p.data_criacao >= ?
                GROUP BY c.id_cliente
                ORDER BY valor_comprado DESC, total_propostas DESC
                LIMIT 5";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $id_usuario, $data_inicio);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $top = [];
        while ($row = $res->fetch_assoc()) {
            $top[] = [
                'nome_cliente' => $row['nome_cliente'],
                'empresa' => $row['empresa'],
                'total_propostas' => (int)$row['total_propostas'],
                'valor_comprado' => (float)$row['valor_comprado']
            ];
        }
        $resultado['top_clientes'] = $top;

        // 7. Produtividade
        $checkTable = $conn->query("SHOW TABLES LIKE 'Tarefas_CRM'");
        if ($checkTable && $checkTable->num_rows > 0) {
            $sql = "SELECT COUNT(*) as total_tarefas,
                        SUM(CASE WHEN status = 'concluida' OR status = 'concluída' THEN 1 ELSE 0 END) as concluidas,
                        SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pendentes
                    FROM Tarefas_CRM 
                    WHERE id_usuario = ? AND created_at >= ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $id_usuario, $data_inicio);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            
            $resultado['produtividade'] = [
                'total_tarefas' => (int)($row['total_tarefas'] ?? 0),
                'concluidas' => (int)($row['concluidas'] ?? 0),
                'pendentes' => (int)($row['pendentes'] ?? 0)
            ];
        } else {
            $resultado['produtividade'] = ['total_tarefas' => 0, 'concluidas' => 0, 'pendentes' => 0];
        }
        
    } catch (Exception $e) {
        return ['sucesso' => false, 'erro' => $e->getMessage()];
    }
    
    return $resultado;
}

function gerarComparativo($conn, $id_usuario, $dias_atual) {
    try {
        $data_inicio_atual = date('Y-m-d H:i:s', strtotime("-$dias_atual days"));
        $data_inicio_anterior = date('Y-m-d H:i:s', strtotime("-".($dias_atual * 2)." days"));
        $data_fim_anterior = date('Y-m-d H:i:s', strtotime("-$dias_atual days"));
        
        // Propostas atual
        $sql = "SELECT COUNT(*) as total FROM Propostas WHERE id_criador = ? AND data_criacao >= ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $id_usuario, $data_inicio_atual);
        $stmt->execute();
        $prop_atual = (int)$stmt->get_result()->fetch_assoc()['total'];
        
        // Propostas anterior
        $sql = "SELECT COUNT(*) as total FROM Propostas WHERE id_criador = ? AND data_criacao >= ? AND data_criacao < ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $id_usuario, $data_inicio_anterior, $data_fim_anterior);
        $stmt->execute();
        $prop_ant = (int)$stmt->get_result()->fetch_assoc()['total'];
        
        // Vendas atual
        $sql = "SELECT COUNT(*) as total FROM Propostas WHERE id_criador = ? AND data_criacao >= ? AND status IN ('Aprovada', 'Finalizada', 'Fechada')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $id_usuario, $data_inicio_atual);
        $stmt->execute();
        $vendas_atual = (int)$stmt->get_result()->fetch_assoc()['total'];
        
        // Vendas anterior
        $sql = "SELECT COUNT(*) as total FROM Propostas WHERE id_criador = ? AND data_criacao >= ? AND data_criacao < ? AND status IN ('Aprovada', 'Finalizada', 'Fechada')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $id_usuario, $data_inicio_anterior, $data_fim_anterior);
        $stmt->execute();
        $vendas_ant = (int)$stmt->get_result()->fetch_assoc()['total'];
        
        // Receita atual
        $sql = "SELECT COALESCE(SUM(valor_final_proposta), 0) as total FROM Propostas WHERE id_criador = ? AND data_criacao >= ? AND status IN ('Aprovada', 'Finalizada', 'Fechada')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $id_usuario, $data_inicio_atual);
        $stmt->execute();
        $rec_atual = (float)$stmt->get_result()->fetch_assoc()['total'];
        
        // Receita anterior
        $sql = "SELECT COALESCE(SUM(valor_final_proposta), 0) as total FROM Propostas WHERE id_criador = ? AND data_criacao >= ? AND data_criacao < ? AND status IN ('Aprovada', 'Finalizada', 'Fechada')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $id_usuario, $data_inicio_anterior, $data_fim_anterior);
        $stmt->execute();
        $rec_ant = (float)$stmt->get_result()->fetch_assoc()['total'];
        
        // Variações
        $var_prop = $prop_ant > 0 ? (($prop_atual - $prop_ant) / $prop_ant) * 100 : ($prop_atual > 0 ? 100 : 0);
        $var_vendas = $vendas_ant > 0 ? (($vendas_atual - $vendas_ant) / $vendas_ant) * 100 : ($vendas_atual > 0 ? 100 : 0);
        $var_rec = $rec_ant > 0 ? (($rec_atual - $rec_ant) / $rec_ant) * 100 : ($rec_atual > 0 ? 100 : 0);
        
        return [
            'sucesso' => true,
            'variacoes' => [
                'propostas' => round($var_prop, 1),
                'vendas' => round($var_vendas, 1),
                'receita' => round($var_rec, 1)
            ]
        ];
        
    } catch (Exception $e) {
        return ['sucesso' => false, 'erro' => $e->getMessage()];
    }
}
