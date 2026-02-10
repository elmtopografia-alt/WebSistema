<?php
//Módulo 3: Dashboard de Analytics
// api/relatorios_api.php - Analytics e relatórios do CRM

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
$periodo = $_GET['periodo'] ?? 'mes'; // hoje, semana, mes, trimestre, ano

// Define intervalo de datas
$intervalos = [
    'hoje' => 'DATE(NOW())',
    'semana' => 'DATE_SUB(NOW(), INTERVAL 7 DAY)',
    'mes' => 'DATE_SUB(NOW(), INTERVAL 30 DAY)',
    'trimestre' => 'DATE_SUB(NOW(), INTERVAL 90 DAY)',
    'ano' => 'DATE_SUB(NOW(), INTERVAL 1 YEAR)'
];

$data_inicio = $intervalos[$periodo] ?? $intervalos['mes'];

try {
    $acao = $_GET['acao'] ?? 'dashboard';
    
    switch ($acao) {
        case 'dashboard':
            // KPIs principais
            $kpis = [];
            
            // 1. Propostas criadas no período
            $stmt = $conn->prepare("
                SELECT COUNT(*) as total, 
                       SUM(valor_total) as valor_total,
                       AVG(valor_total) as ticket_medio
                FROM Propostas 
                WHERE id_criador = ? AND data_criacao >= {$data_inicio}
            ");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $kpis['propostas_criadas'] = $stmt->get_result()->fetch_assoc();

            // 2. Taxa de conversão
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'Aprovada' THEN 1 ELSE 0 END) as aprovadas,
                    SUM(CASE WHEN status = 'Cancelada' THEN 1 ELSE 0 END) as perdidas,
                    SUM(CASE WHEN status = 'Aprovada' THEN valor_total ELSE 0 END) as valor_ganho,
                    SUM(CASE WHEN status = 'Cancelada' THEN valor_total ELSE 0 END) as valor_perdido
                FROM Propostas 
                WHERE id_criador = ? AND data_criacao >= {$data_inicio}
            ");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $conversao = $stmt->get_result()->fetch_assoc();
            
            $kpis['conversao'] = [
                'taxa' => $conversao['total'] > 0 ? round(($conversao['aprovadas'] / $conversao['total']) * 100, 1) : 0,
                'total' => $conversao['total'],
                'aprovadas' => $conversao['aprovadas'],
                'perdidas' => $conversao['perdidas'],
                'valor_ganho' => $conversao['valor_ganho'],
                'valor_perdido' => $conversao['valor_perdido']
            ];

            // 3. Tempo médio de conversão (dias entre criação e aprovação)
            $stmt = $conn->prepare("
                SELECT AVG(DATEDIFF(data_atualizacao, data_criacao)) as dias_medio
                FROM Propostas 
                WHERE id_criador = ? 
                AND status = 'Aprovada' 
                AND data_criacao >= {$data_inicio}
                AND data_atualizacao IS NOT NULL
            ");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $kpis['tempo_conversao'] = $stmt->get_result()->fetch_assoc()['dias_medio'] ?? 0;

            // 4. Funil de vendas (distribuição por status)
            $stmt = $conn->prepare("
                SELECT status, COUNT(*) as quantidade, SUM(valor_total) as valor
                FROM Propostas 
                WHERE id_criador = ? AND data_criacao >= {$data_inicio}
                GROUP BY status
                ORDER BY FIELD(status, 'Em Elaboração', 'Enviada', 'Aprovada', 'Cancelada', 'Finalizada')
            ");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $funil = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // 5. Top clientes (mais propostas ou maior valor)
            $stmt = $conn->prepare("
                SELECT 
                    c.id_cliente,
                    c.nome_cliente,
                    c.empresa,
                    COUNT(p.id_proposta) as total_propostas,
                    SUM(CASE WHEN p.status = 'Aprovada' THEN p.valor_total ELSE 0 END) as valor_comprado,
                    SUM(p.valor_total) as valor_total_propostas
                FROM Clientes c
                JOIN Propostas p ON c.id_cliente = p.id_cliente
                WHERE p.id_criador = ? AND p.data_criacao >= {$data_inicio}
                GROUP BY c.id_cliente
                ORDER BY valor_comprado DESC
                LIMIT 10
            ");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $top_clientes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // 6. Evolução temporal (propostas por dia/semana)
            $group_by = $periodo === 'hoje' ? 'HOUR' : ($periodo === 'semana' ? 'DAY' : 'WEEK');
            
            $stmt = $conn->prepare("
                SELECT 
                    DATE(data_criacao) as data,
                    COUNT(*) as propostas,
                    SUM(CASE WHEN status = 'Aprovada' THEN 1 ELSE 0 END) as aprovadas,
                    SUM(valor_total) as valor_total
                FROM Propostas 
                WHERE id_criador = ? AND data_criacao >= {$data_inicio}
                GROUP BY DATE(data_criacao)
                ORDER BY data
            ");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $evolucao = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // 7. Produtividade (tarefas concluídas)
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(*) as total_tarefas,
                    COUNT(CASE WHEN status = 'concluida' THEN 1 END) as concluidas,
                    COUNT(CASE WHEN status = 'pendente' THEN 1 END) as pendentes,
                    AVG(CASE WHEN status = 'concluida' THEN DATEDIFF(data_conclusao, created_at) END) as tempo_medio_conclusao
                FROM Tarefas_CRM 
                WHERE id_usuario = ? AND created_at >= {$data_inicio}
            ");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $produtividade = $stmt->get_result()->fetch_assoc();

            echo json_encode([
                'sucesso' => true,
                'periodo' => $periodo,
                'kpis' => $kpis,
                'funil' => $funil,
                'top_clientes' => $top_clientes,
                'evolucao' => $evolucao,
                'produtividade' => $produtividade
            ]);
            break;

        case 'comparativo':
            // Compara período atual vs anterior
            $dias = intval($_GET['dias'] ?? 30);
            
            // Período atual
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(*) as propostas,
                    SUM(CASE WHEN status = 'Aprovada' THEN 1 ELSE 0 END) as vendas,
                    SUM(CASE WHEN status = 'Aprovada' THEN valor_total ELSE 0 END) as receita
                FROM Propostas 
                WHERE id_criador = ? AND data_criacao >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->bind_param("ii", $id_usuario, $dias);
            $stmt->execute();
            $atual = $stmt->get_result()->fetch_assoc();

            // Período anterior (mesma quantidade de dias antes)
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(*) as propostas,
                    SUM(CASE WHEN status = 'Aprovada' THEN 1 ELSE 0 END) as vendas,
                    SUM(CASE WHEN status = 'Aprovada' THEN valor_total ELSE 0 END) as receita
                FROM Propostas 
                WHERE id_criador = ? 
                AND data_criacao BETWEEN DATE_SUB(NOW(), INTERVAL ? DAY) AND DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $dias_dobro = $dias * 2;
            $stmt->bind_param("iii", $id_usuario, $dias_dobro, $dias);
            $stmt->execute();
            $anterior = $stmt->get_result()->fetch_assoc();

            // Calcula variações percentuais
            $calcVariacao = function($atual, $anterior) {
                if ($anterior == 0) return $atual > 0 ? 100 : 0;
                return round((($atual - $anterior) / $anterior) * 100, 1);
            };

            echo json_encode([
                'sucesso' => true,
                'variacoes' => [
                    'propostas' => $calcVariacao($atual['propostas'], $anterior['propostas']),
                    'vendas' => $calcVariacao($atual['vendas'], $anterior['vendas']),
                    'receita' => $calcVariacao($atual['receita'], $anterior['receita'])
                ],
                'atual' => $atual,
                'anterior' => $anterior
            ]);
            break;

        default:
            throw new Exception("Ação inválida");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['erro' => $e->getMessage()]);
}
?>