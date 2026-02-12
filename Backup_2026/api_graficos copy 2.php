<?php
// Inicio: api_graficos.php
// Função: Motor de dados JSON (Versão Híbrida: Lógica Antiga + Smart Start)

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0); 
error_reporting(0);

session_start();
require_once 'config.php';
require_once 'db.php';

// Estrutura de Resposta
$dados = [
    'grafico_linha' => [
        'labels' => [],
        'series' => [
            'Aprovada' => [],
            'Enviada' => [],
            'Em elaboração' => [],
            'Cancelada' => []
        ]
    ],
    'status_pizza' => [
        'Aprovada' => 0, 'Enviada' => 0, 'Em elaboração' => 0, 'Cancelada' => 0
    ],
    'resumo_financeiro' => [
        'Aprovada' => 0, 'Enviada' => 0, 'Em elaboração' => 0, 'Cancelada' => 0
    ],
    'erro' => false
];

try {
    if (!isset($_SESSION['usuario_id'])) { throw new Exception("Não logado"); }

    $id_usuario = $_SESSION['usuario_id'];
    $conn = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') ? Database::getDemo() : Database::getProd();

    // ==========================================================
    // 1. SMART START (Data da Última Proposta - 6 Meses)
    // ==========================================================
    
    // Busca a data da última proposta
    $sqlMax = "SELECT MAX(data_criacao) as max_data FROM Propostas WHERE id_criador = ?";
    $stmtMax = $conn->prepare($sqlMax);
    $stmtMax->bind_param('i', $id_usuario);
    $stmtMax->execute();
    $resMax = $stmtMax->get_result()->fetch_assoc();
    
    // Se tiver dados, usa a última data. Se não, usa HOJE.
    $dataFim = $resMax['max_data'] ? new DateTime($resMax['max_data']) : new DateTime();
    $dataFim->modify('last day of this month');
    $dataFim->setTime(23, 59, 59);
    
    // Define Início = Fim - 5 meses (Total 6 meses)
    $dataInicio = clone $dataFim;
    $dataInicio->modify('-5 months');
    $dataInicio->modify('first day of this month');
    $dataInicio->setTime(0, 0, 0);
    
    $strInicio = $dataInicio->format('Y-m-d H:i:s');
    $strFim = $dataFim->format('Y-m-d H:i:s');

    // ==========================================================
    // 2. PREPARAÇÃO DO ARRAY (Garante Labels Cronológicos)
    // ==========================================================
    $mapa_indices = [];
    $periodo = new DatePeriod($dataInicio, new DateInterval('P1M'), $dataFim);

    foreach ($periodo as $dt) {
        $chave = $dt->format('Y-m');
        $label = $dt->format('m/Y');
        
        $dados['grafico_linha']['labels'][] = $label;
        $idx = count($dados['grafico_linha']['labels']) - 1;
        $mapa_indices[$chave] = $idx;

        // Inicializa com 0
        foreach ($dados['grafico_linha']['series'] as $k => $v) {
            $dados['grafico_linha']['series'][$k][$idx] = 0;
        }
    }

    // ==========================================================
    // 3. BUSCA DADOS (Query Agrupada por Status)
    // ==========================================================
    $sqlFat = "SELECT 
                DATE_FORMAT(data_criacao, '%Y-%m') as mes_chave,
                status,
                SUM(valor_final_proposta) as total
               FROM Propostas 
               WHERE id_criador = ? 
               AND data_criacao BETWEEN ? AND ?
               GROUP BY mes_chave, status";
    
    $stmt = $conn->prepare($sqlFat);
    $stmt->bind_param('iss', $id_usuario, $strInicio, $strFim);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $mes = $row['mes_chave']; 
        $st_bruto = mb_strtolower($row['status'], 'UTF-8');
        $valor = (float)$row['total'];

        // Normalização de Status
        $chave_destino = 'Em elaboração';
        if (strpos($st_bruto, 'aprov') !== false || strpos($st_bruto, 'conclu') !== false || strpos($st_bruto, 'aceita') !== false) $chave_destino = 'Aprovada';
        elseif (strpos($st_bruto, 'envia') !== false) $chave_destino = 'Enviada';
        elseif (strpos($st_bruto, 'cancel') !== false) $chave_destino = 'Cancelada';

        // Preenche Gráfico de Linha
        if (isset($mapa_indices[$mes])) {
            $i = $mapa_indices[$mes];
            $dados['grafico_linha']['series'][$chave_destino][$i] += $valor;
        }

        // Preenche Resumo Financeiro e Pizza (Acumulado do Período)
        $dados['status_pizza'][$chave_destino] += 1; // Contagem para Pizza
        $dados['resumo_financeiro'][$chave_destino] += $valor; // Valor para Resumo
    }
    
    // Correção Pizza: A query acima soma por mês/status. 
    // Para a Pizza precisamos da contagem total correta. 
    // Vamos fazer uma query separada para garantir a contagem exata de ITENS, não de iterações do loop.
    
    // Reset para garantir
    $dados['status_pizza'] = ['Aprovada' => 0, 'Enviada' => 0, 'Em elaboração' => 0, 'Cancelada' => 0];
    
    $sqlPizza = "SELECT status, COUNT(*) as qtd FROM Propostas 
                 WHERE id_criador = ? 
                 AND data_criacao BETWEEN ? AND ?
                 GROUP BY status";
    $stmtP = $conn->prepare($sqlPizza);
    $stmtP->bind_param('iss', $id_usuario, $strInicio, $strFim);
    $stmtP->execute();
    $resP = $stmtP->get_result();
    
    while ($row = $resP->fetch_assoc()) {
        $st_bruto = mb_strtolower($row['status'], 'UTF-8');
        $qtd = (int)$row['qtd'];
        
        $chave_destino = 'Em elaboração';
        if (strpos($st_bruto, 'aprov') !== false || strpos($st_bruto, 'conclu') !== false || strpos($st_bruto, 'aceita') !== false) $chave_destino = 'Aprovada';
        elseif (strpos($st_bruto, 'envia') !== false) $chave_destino = 'Enviada';
        elseif (strpos($st_bruto, 'cancel') !== false) $chave_destino = 'Cancelada';
        
        $dados['status_pizza'][$chave_destino] += $qtd;
    }

} catch (Exception $e) {
    $dados['erro'] = $e->getMessage();
}

echo json_encode($dados);
exit;
?>