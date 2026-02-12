<?php
// Inicio: api_graficos.php
// Função: Motor de Inteligência de Dados (JSON) para o Painel
// Correção: Normalização agressiva de strings para garantir contagem correta.

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0); 
error_reporting(0); 

session_start();
require_once 'config.php';
require_once 'db.php';

$dados = [
    'grafico_linha' => [
        'labels' => [], 
        'series' => [
            'Aprovada' => [], 'Enviada' => [], 'Em elaboração' => [], 'Cancelada' => []
        ]
    ],
    'status_pizza' => [
        'Aprovada' => 0, 'Enviada' => 0, 'Em elaboração' => 0, 'Cancelada' => 0
    ],
    'erro' => false
];

try {
    if (!isset($_SESSION['usuario_id'])) { throw new Exception("Não logado"); }

    $id_usuario = $_SESSION['usuario_id'];
    $conn = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') ? Database::getDemo() : Database::getProd();

    // 1. LINHA DO TEMPO (6 Meses)
    $mapa_indices = [];
    for ($i = 5; $i >= 0; $i--) {
        $time = strtotime("-$i months");
        $chave_ano_mes = date('Y-m', $time); 
        $label_visual = date('m/Y', $time);
        
        $dados['grafico_linha']['labels'][] = $label_visual;
        $idx = count($dados['grafico_linha']['labels']) - 1;
        $mapa_indices[$chave_ano_mes] = $idx;

        foreach ($dados['grafico_linha']['series'] as $k => $v) {
            $dados['grafico_linha']['series'][$k][$idx] = 0;
        }
    }

    // 2. DADOS DE LINHA (Faturamento)
    $sqlFat = "SELECT DATE_FORMAT(data_criacao, '%Y-%m') as mes_chave, status, SUM(valor_final_proposta) as total
               FROM Propostas WHERE id_criador = ? 
               AND data_criacao >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 6 MONTH), '%Y-%m-01')
               GROUP BY mes_chave, status";
    
    $stmt = $conn->prepare($sqlFat);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $mes = $row['mes_chave'];
        $st = mb_strtolower(trim($row['status']), 'UTF-8'); // Normalização total
        $val = (float)$row['total'];

        // Lógica de Agrupamento Flexível
        $chave = 'Em elaboração'; // Default
        if (strpos($st, 'aprov') !== false || strpos($st, 'conclu') !== false || strpos($st, 'aceita') !== false) $chave = 'Aprovada';
        elseif (strpos($st, 'envia') !== false) $chave = 'Enviada';
        elseif (strpos($st, 'cancel') !== false || strpos($st, 'perdid') !== false) $chave = 'Cancelada';

        if (isset($mapa_indices[$mes])) {
            $idx = $mapa_indices[$mes];
            $dados['grafico_linha']['series'][$chave][$idx] += $val;
        }
    }

    // 3. DADOS DE PIZZA (Quantidade)
    $sqlPizza = "SELECT status, COUNT(*) as qtd FROM Propostas WHERE id_criador = ? GROUP BY status";
    $stmtP = $conn->prepare($sqlPizza);
    $stmtP->bind_param('i', $id_usuario);
    $stmtP->execute();
    $resP = $stmtP->get_result();

    while ($row = $resP->fetch_assoc()) {
        $st = mb_strtolower(trim($row['status']), 'UTF-8');
        $qtd = (int)$row['qtd'];

        if (strpos($st, 'aprov') !== false || strpos($st, 'conclu') !== false || strpos($st, 'aceita') !== false) $dados['status_pizza']['Aprovada'] += $qtd;
        elseif (strpos($st, 'envia') !== false) $dados['status_pizza']['Enviada'] += $qtd;
        elseif (strpos($st, 'cancel') !== false || strpos($st, 'perdid') !== false) $dados['status_pizza']['Cancelada'] += $qtd;
        else $dados['status_pizza']['Em elaboração'] += $qtd;
    }

} catch (Exception $e) { $dados['erro'] = $e->getMessage(); }

echo json_encode($dados);
exit;
// Fim: api_graficos.php
?>