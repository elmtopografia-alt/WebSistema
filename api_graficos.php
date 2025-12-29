<?php
// Nome do Arquivo: api_graficos.php
// Função: API JSON Restaurada. Garante zeros onde não tem dados para o gráfico não sumir.

header('Content-Type: application/json');
ini_set('display_errors', 0); // Garante que erros de PHP não sujem o JSON
error_reporting(E_ALL);

session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['erro' => 'Acesso negado']);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$is_demo = ($_SESSION['ambiente'] === 'demo');
$conn = $is_demo ? Database::getDemo() : Database::getProd();

$dados = [
    'grafico_linha' => [],
    'status_pizza' => [],
    'kpis' => []
];

try {
    // 1. DADOS DE LINHA (Faturamento)
    // Prepara os últimos 6 meses com ZERO (para garantir que o gráfico desenhe algo)
    $periodo = [];
    for ($i = 5; $i >= 0; $i--) {
        $key = date('Y-m', strtotime("-$i months"));
        $label = date('m/Y', strtotime("-$i months"));
        $periodo[$key] = [
            'label' => $label,
            'total_orcado' => 0,
            'total_aprovado' => 0
        ];
    }

    // Busca dados reais e preenche
    $sqlFat = "SELECT 
                DATE_FORMAT(data_criacao, '%Y-%m') as mes_ano,
                SUM(valor_final_proposta) as total_geral,
                SUM(CASE WHEN status = 'Aprovada' THEN valor_final_proposta ELSE 0 END) as total_aprovado
               FROM Propostas 
               WHERE id_criador = ? 
               AND data_criacao >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
               GROUP BY mes_ano";
    
    $stmt = $conn->prepare($sqlFat);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $res = $stmt->get_result();
    
    while ($row = $res->fetch_assoc()) {
        if (isset($periodo[$row['mes_ano']])) {
            $periodo[$row['mes_ano']]['total_orcado'] = (float)$row['total_geral'];
            $periodo[$row['mes_ano']]['total_aprovado'] = (float)$row['total_aprovado'];
        }
    }
    // Transforma em array indexado para o ChartJS ler sem erro
    $dados['grafico_linha'] = array_values($periodo);

    // 2. DADOS DE PIZZA (Status)
    // Inicializa fixo para não dar erro se estiver vazio
    $pizza_base = [
        'Aprovada' => 0, 'Enviada' => 0, 'Em elaboração' => 0, 'Cancelada' => 0
    ];
    
    $sqlStatus = "SELECT status, COUNT(*) as qtd FROM Propostas WHERE id_criador = ? GROUP BY status";
    $stmt = $conn->prepare($sqlStatus);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        // Mapeia apenas os status conhecidos
        if (isset($pizza_base[$row['status']])) {
            $pizza_base[$row['status']] = (int)$row['qtd'];
        }
    }
    $dados['status_pizza'] = $pizza_base;

    // 3. KPIs
    $sqlKPI = "SELECT 
                COUNT(*) as qtd_total,
                SUM(valor_final_proposta) as soma_geral,
                SUM(CASE WHEN status = 'Aprovada' THEN valor_final_proposta ELSE 0 END) as soma_aprovada,
                AVG(valor_final_proposta) as media
               FROM Propostas WHERE id_criador = ?";
    $stmt = $conn->prepare($sqlKPI);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $rowKPI = $stmt->get_result()->fetch_assoc();
    
    $dados['kpis'] = [
        'receita_real' => (float)$rowKPI['soma_aprovada'],
        'volume_orcado' => (float)$rowKPI['soma_geral'],
        'ticket_medio'  => (float)$rowKPI['media']
    ];

    echo json_encode($dados);

} catch (Exception $e) {
    // Retorna erro JSON válido para não quebrar o JS
    echo json_encode(['erro' => $e->getMessage()]);
}
?>