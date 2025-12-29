<?php
// Nome do Arquivo: api_graficos.php
// Função: API JSON. Agora retorna NULL em meses vazios para criar linha de tendência contínua (SpanGaps).

header('Content-Type: application/json');
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
    // 1. CRONOLOGIA
    $sqlInicio = "SELECT MIN(data_criacao) as primeira_data FROM Propostas WHERE id_criador = ?";
    $stmtIn = $conn->prepare($sqlInicio);
    $stmtIn->bind_param('i', $id_usuario);
    $stmtIn->execute();
    $rowIn = $stmtIn->get_result()->fetch_assoc();

    $meses_para_mostrar = 6; 
    if ($rowIn && !empty($rowIn['primeira_data'])) {
        $intervalo = (new DateTime($rowIn['primeira_data']))->diff(new DateTime());
        $meses_vida = ($intervalo->y * 12) + $intervalo->m;
        if ($meses_vida < 5) $meses_para_mostrar = $meses_vida + 1;
    } else {
        $meses_para_mostrar = 2; // Mínimo para fazer uma linha
    }

    // 2. PREPARA ARRAYS COM NULL (O SEGREDO DO GRÁFICO)
    // Usamos NULL em vez de 0. O Chart.js ignora NULL e liga os pontos vizinhos.
    $periodo = [];
    for ($i = $meses_para_mostrar; $i >= 0; $i--) {
        $key = date('Y-m', strtotime("-$i months"));
        $label = date('m/Y', strtotime("-$i months"));
        
        $periodo[$key] = [
            'label' => $label,
            'total_orcado' => null,   // NULL = Não desenha ponto no zero
            'total_aprovado' => null 
        ];
    }

    // 3. BUSCA DADOS
    $sqlFat = "SELECT 
                DATE_FORMAT(data_criacao, '%Y-%m') as mes_ano,
                SUM(valor_final_proposta) as total_geral,
                SUM(CASE WHEN status = 'Aprovada' THEN valor_final_proposta ELSE 0 END) as total_aprovado
               FROM Propostas 
               WHERE id_criador = ? 
               AND data_criacao >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
               GROUP BY DATE_FORMAT(data_criacao, '%Y-%m')";
    
    $stmt = $conn->prepare($sqlFat);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $res = $stmt->get_result();
    
    while ($row = $res->fetch_assoc()) {
        if (isset($periodo[$row['mes_ano']])) {
            // Se tem valor, coloca o float. Se for zero mesmo no banco, vira float.
            $valor_orc = (float)$row['total_geral'];
            $valor_apr = (float)$row['total_aprovado'];
            
            // Só preenche se for maior que zero, senão deixa null para ligar pontos
            $periodo[$row['mes_ano']]['total_orcado'] = $valor_orc > 0 ? $valor_orc : null;
            $periodo[$row['mes_ano']]['total_aprovado'] = $valor_apr > 0 ? $valor_apr : null;
        }
    }
    
    // Tratamento Final: Se o primeiro ou último mês forem NULL, colocamos 0 para a linha não ficar "voando" infinita
    // Mas os do meio ficam NULL para fazer a ponte.
    $keys = array_keys($periodo);
    $firstKey = $keys[0];
    $lastKey = end($keys);
    
    if ($periodo[$firstKey]['total_orcado'] === null) $periodo[$firstKey]['total_orcado'] = 0;
    if ($periodo[$firstKey]['total_aprovado'] === null) $periodo[$firstKey]['total_aprovado'] = 0;
    
    if ($periodo[$lastKey]['total_orcado'] === null) $periodo[$lastKey]['total_orcado'] = 0;
    if ($periodo[$lastKey]['total_aprovado'] === null) $periodo[$lastKey]['total_aprovado'] = 0;

    $dados['grafico_linha'] = array_values($periodo);

    // 4. PIZZA
    $sqlStatus = "SELECT status, COUNT(*) as qtd FROM Propostas WHERE id_criador = ? GROUP BY status";
    $stmt = $conn->prepare($sqlStatus);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) { $dados['status_propostas'][] = $row; }

    // 5. KPIs
    $sqlKPI = "SELECT 
                COUNT(*) as qtd_total,
                SUM(valor_final_proposta) as soma_geral,
                SUM(CASE WHEN status = 'Aprovada' THEN valor_final_proposta ELSE 0 END) as soma_aprovada,
                AVG(valor_final_proposta) as media
               FROM Propostas WHERE id_criador = ?";
    $stmt = $conn->prepare($sqlKPI);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $dados['kpis'] = $stmt->get_result()->fetch_assoc();

    echo json_encode($dados);

} catch (Exception $e) { echo json_encode(['erro' => $e->getMessage()]); }
?>