<?php
// Inicio: api_graficos.php
// Função: Motor de dados JSON (Versão Blindada)
// Correção: Remove dependências de IntlDateFormatter e garante zeros onde não há dados.

// 1. Configurações de Cabeçalho para evitar lixo no JSON
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0); 
error_reporting(0); // Silencia erros não fatais que quebram o gráfico

session_start();
require_once 'config.php';
require_once 'db.php';

// Resposta padrão em caso de falha crítica
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
    'erro' => false
];

try {
    if (!isset($_SESSION['usuario_id'])) { throw new Exception("Não logado"); }

    $id_usuario = $_SESSION['usuario_id'];
    $conn = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') ? Database::getDemo() : Database::getProd();

    // ==========================================================
    // 1. LINHA DO TEMPO (6 Meses) - Lógica PHP Pura
    // ==========================================================
    $mapa_indices = [];
    
    // Gera os últimos 6 meses (do mais antigo para o atual)
    for ($i = 5; $i >= 0; $i--) {
        $mes_num = date('m', strtotime("-$i months"));
        $ano_num = date('Y', strtotime("-$i months"));
        $chave = $ano_num . '-' . $mes_num; // Ex: 2025-08
        
        // Label simplificado (Ex: 08/2025) para não depender de locale
        $label = $mes_num . '/' . $ano_num; 
        
        $dados['grafico_linha']['labels'][] = $label;
        $idx = count($dados['grafico_linha']['labels']) - 1;
        $mapa_indices[$chave] = $idx;

        // Inicializa com 0.00
        foreach ($dados['grafico_linha']['series'] as $k => $v) {
            $dados['grafico_linha']['series'][$k][$idx] = 0;
        }
    }

    // ==========================================================
    // 2. BUSCA DADOS NO BANCO (Linha)
    // ==========================================================
    $sqlFat = "SELECT 
                DATE_FORMAT(data_criacao, '%Y-%m') as mes_chave,
                status,
                SUM(valor_final_proposta) as total
               FROM Propostas 
               WHERE id_criador = ? 
               AND data_criacao >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
               GROUP BY mes_chave, status";
    
    $stmt = $conn->prepare($sqlFat);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $mes = $row['mes_chave']; // 2025-08
        $st_bruto = mb_strtolower($row['status'], 'UTF-8');
        $valor = (float)$row['total'];

        // Mapeia para as chaves exatas do JS
        $chave_destino = 'Em elaboração';
        if (strpos($st_bruto, 'aprov') !== false || strpos($st_bruto, 'conclu') !== false) $chave_destino = 'Aprovada';
        elseif (strpos($st_bruto, 'envia') !== false) $chave_destino = 'Enviada';
        elseif (strpos($st_bruto, 'cancel') !== false) $chave_destino = 'Cancelada';

        // Preenche o slot correto
        if (isset($mapa_indices[$mes])) {
            $i = $mapa_indices[$mes];
            $dados['grafico_linha']['series'][$chave_destino][$i] += $valor;
        }
    }

    // ==========================================================
    // 3. BUSCA DADOS DE PIZZA (Totais)
    // ==========================================================
    $sqlPizza = "SELECT status, COUNT(*) as qtd FROM Propostas WHERE id_criador = ? GROUP BY status";
    $stmtP = $conn->prepare($sqlPizza);
    $stmtP->bind_param('i', $id_usuario);
    $stmtP->execute();
    $resP = $stmtP->get_result();

    while ($row = $resP->fetch_assoc()) {
        $st_bruto = mb_strtolower($row['status'], 'UTF-8');
        $qtd = (int)$row['qtd'];

        if (strpos($st_bruto, 'aprov') !== false || strpos($st_bruto, 'conclu') !== false) $dados['status_pizza']['Aprovada'] += $qtd;
        elseif (strpos($st_bruto, 'envia') !== false) $dados['status_pizza']['Enviada'] += $qtd;
        elseif (strpos($st_bruto, 'cancel') !== false) $dados['status_pizza']['Cancelada'] += $qtd;
        else $dados['status_pizza']['Em elaboração'] += $qtd;
    }

} catch (Exception $e) {
    $dados['erro'] = $e->getMessage();
}

// Saída Final Limpa
echo json_encode($dados);
exit;
// Fim: api_graficos.php
?>