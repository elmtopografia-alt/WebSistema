<?php
// Inicios: api_graficos.php
// Função: Motor de dados JSON (Multi-Status / Multi-Linhas)

header('Content-Type: application/json');
ini_set('display_errors', 0); 
error_reporting(E_ALL);

session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) { echo json_encode(['erro' => 'Acesso negado']); exit; }

$id_usuario = $_SESSION['usuario_id'];
$ambiente = $_SESSION['ambiente'] ?? 'producao';
$conn = ($ambiente === 'demo') ? Database::getDemo() : Database::getProd();
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');

$dados = [
    'grafico_linha' => [
        'labels' => [],
        'series' => [
            'Aprovada'      => [],
            'Enviada'       => [],
            'Em elaboração' => [],
            'Cancelada'     => []
        ]
    ],
    'status_pizza' => [],
    'kpis' => []
];

try {
    // 1. PREPARAÇÃO DA LINHA DO TEMPO (6 Meses)
    $mapa_meses = [];
    
    for ($i = 5; $i >= 0; $i--) {
        $dt = new DateTime("-$i months");
        $chave_mes = $dt->format('Y-m'); // Ex: 2025-08
        
        // Formata label (Ex: AGO)
        if (class_exists('IntlDateFormatter')) {
            $fmt = new IntlDateFormatter('pt_BR', IntlDateFormatter::NONE, IntlDateFormatter::NONE);
            $fmt->setPattern('MMM');
            $label = strtoupper($fmt->format($dt));
        } else {
            $label = $dt->format('M');
        }
        
        $dados['grafico_linha']['labels'][] = $label;
        $mapa_meses[$chave_mes] = count($dados['grafico_linha']['labels']) - 1; // Guarda o índice (0 a 5)
        
        // Inicializa com zero
        foreach ($dados['grafico_linha']['series'] as $key => $val) {
            $dados['grafico_linha']['series'][$key][] = 0;
        }
    }

    // 2. BUSCA DADOS MULTI-LINHAS (Agrupado por Mês E Status)
    $sqlFat = "SELECT 
                DATE_FORMAT(data_criacao, '%Y-%m') as mes_ano,
                status,
                SUM(valor_final_proposta) as total
               FROM Propostas 
               WHERE id_criador = ? 
               AND data_criacao >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 6 MONTH), '%Y-%m-01')
               GROUP BY mes_ano, status";
    
    $stmt = $conn->prepare($sqlFat);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $mes = $row['mes_ano'];
        $status_bruto = $row['status'];
        $valor = (float)$row['total'];
        
        // Normaliza Status
        $status_key = 'Em elaboração'; // Default
        if (stripos($status_bruto, 'aprov') !== false) $status_key = 'Aprovada';
        elseif (stripos($status_bruto, 'envia') !== false) $status_key = 'Enviada';
        elseif (stripos($status_bruto, 'cancel') !== false) $status_key = 'Cancelada';
        
        // Se o mês existe no nosso mapa de 6 meses, preenche o valor no índice correto
        if (isset($mapa_meses[$mes])) {
            $indice = $mapa_meses[$mes];
            // Acumula valor (caso tenha 'Aprovada' e 'Concluída' no mesmo mês, soma ambos na chave Aprovada)
            $dados['grafico_linha']['series'][$status_key][$indice] += $valor;
        }
    }

    // 3. PIZZA E KPIs (Mantidos iguais para consistência)
    $pizza_base = ['Aprovada'=>0, 'Enviada'=>0, 'Em elaboração'=>0, 'Cancelada'=>0];
    $sqlStatus = "SELECT status, COUNT(*) as qtd FROM Propostas WHERE id_criador = ? GROUP BY status";
    $stmt = $conn->prepare($sqlStatus); $stmt->bind_param('i', $id_usuario); $stmt->execute(); $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $st = $row['status'];
        if (stripos($st, 'aprov')!==false) $pizza_base['Aprovada'] += $row['qtd'];
        elseif (stripos($st, 'envia')!==false) $pizza_base['Enviada'] += $row['qtd'];
        elseif (stripos($st, 'cancel')!==false) $pizza_base['Cancelada'] += $row['qtd'];
        else $pizza_base['Em elaboração'] += $row['qtd'];
    }
    $dados['status_pizza'] = $pizza_base;

    echo json_encode($dados);

} catch (Exception $e) { echo json_encode(['erro' => $e->getMessage()]); }
// Fim: api_graficos.php
?>