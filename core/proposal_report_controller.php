<?php
/**
 * core/proposal_report_controller.php
 * Controlador do Relatório Detalhado da Proposta
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['usuario_id'])) { 
    header("Location: login.php"); 
    exit; 
}

if (!isset($_GET['id'])) { 
    die("ID da proposta não fornecido."); 
}

$id_proposta = intval($_GET['id']);
$id_usuario = $_SESSION['usuario_id'];
$is_demo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');
$conn = $is_demo ? Database::getDemo() : Database::getProd();

// 1. Busca Dados da Proposta
$sql = "SELECT p.*, c.nome_cliente, s.nome as nome_servico 
        FROM Propostas p 
        LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente 
        LEFT JOIN Tipo_Servicos s ON p.id_servico = s.id_servico 
        WHERE p.id_proposta = ? AND p.id_criador = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $id_proposta, $id_usuario);
$stmt->execute();
$proposta = $stmt->get_result()->fetch_assoc();

if (!$proposta) { 
    die("Proposta não encontrada ou acesso negado."); 
}

// 2. Funções Auxiliares
function getCustos($conn, $tabela, $id_proposta) {
    $sql = "SELECT * FROM $tabela WHERE id_proposta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_proposta);
    $stmt->execute();
    return $stmt->get_result();
}

// 3. Busca e Calcula Custos
$salarios = getCustos($conn, 'Proposta_Salarios', $id_proposta);
$estadia = getCustos($conn, 'Proposta_Estadia', $id_proposta);
$consumos = getCustos($conn, 'Proposta_Consumos', $id_proposta);
$admin = getCustos($conn, 'Proposta_Custos_Administrativos', $id_proposta);

// Locação (JOIN específico)
$sql_loc = "SELECT pl.*, tl.nome as nome_equipamento, m.nome_marca 
            FROM Proposta_Locacao pl 
            LEFT JOIN Tipo_Locacao tl ON pl.id_locacao = tl.id_locacao 
            LEFT JOIN Marcas m ON pl.id_marca = m.id_marca 
            WHERE pl.id_proposta = ?";
$stmt_loc = $conn->prepare($sql_loc);
$stmt_loc->bind_param('i', $id_proposta);
$stmt_loc->execute();
$locacao = $stmt_loc->get_result();

// Processamento
$total_salarios = 0; $dados_salarios = [];
while($r = $salarios->fetch_assoc()) {
    $custo = ($r['quantidade'] * $r['salario_base'] * $r['fator_encargos'] / 30) * $r['dias'];
    $r['custo_calculado'] = $custo;
    $dados_salarios[] = $r;
    $total_salarios += $custo;
}

$total_estadia = 0; $dados_estadia = [];
while($r = $estadia->fetch_assoc()) {
    $custo = $r['quantidade'] * $r['valor_unitario'] * $r['dias'];
    $r['custo_calculado'] = $custo;
    $dados_estadia[] = $r;
    $total_estadia += $custo;
}

$total_consumos = 0; $dados_consumos = [];
while($r = $consumos->fetch_assoc()) {
    $kml = $r['consumo_kml'] > 0 ? $r['consumo_kml'] : 1;
    $custo = ($r['km_total'] * $r['valor_litro'] / $kml) * $r['quantidade'];
    $r['custo_calculado'] = $custo;
    $dados_consumos[] = $r;
    $total_consumos += $custo;
}

$total_locacao = 0; $dados_locacao = [];
while($r = $locacao->fetch_assoc()) {
    $custo = ($r['quantidade'] * $r['valor_mensal'] / 30) * $r['dias'];
    $r['custo_calculado'] = $custo;
    $dados_locacao[] = $r;
    $total_locacao += $custo;
}

$total_admin = 0; $dados_admin = [];
while($r = $admin->fetch_assoc()) {
    $custo = $r['quantidade'] * $r['valor'];
    $r['custo_calculado'] = $custo;
    $dados_admin[] = $r;
    $total_admin += $custo;
}

// Totais Finais
$total_custos = $total_salarios + $total_estadia + $total_consumos + $total_locacao + $total_admin;
$receita_bruta = $proposta['valor_final_proposta'];
$lucro_real = $receita_bruta - $total_custos;
$margem_real = ($receita_bruta > 0) ? ($lucro_real / $receita_bruta) * 100 : 0;

// Chart Data
$chart_labels = [];
$chart_data = [];
$chart_colors = [];

if ($total_salarios > 0) { $chart_labels[] = 'Equipe'; $chart_data[] = $total_salarios; $chart_colors[] = '#0ea5e9'; } // Sky
if ($total_estadia > 0) { $chart_labels[] = 'Estadia'; $chart_data[] = $total_estadia; $chart_colors[] = '#8b5cf6'; } // Violet
if ($total_consumos > 0) { $chart_labels[] = 'Combustível'; $chart_data[] = $total_consumos; $chart_colors[] = '#f97316'; } // Orange
if ($total_locacao > 0) { $chart_labels[] = 'Equipamentos'; $chart_data[] = $total_locacao; $chart_colors[] = '#eab308'; } // Yellow
if ($total_admin > 0) { $chart_labels[] = 'Admin'; $chart_data[] = $total_admin; $chart_colors[] = '#64748b'; } // Slate

return [
    'proposta' => $proposta,
    'dados' => [
        'salarios' => $dados_salarios,
        'estadia' => $dados_estadia,
        'consumos' => $dados_consumos,
        'locacao' => $dados_locacao,
        'admin' => $dados_admin
    ],
    'totais' => [
        'salarios' => $total_salarios,
        'estadia' => $total_estadia,
        'consumos' => $total_consumos,
        'locacao' => $total_locacao,
        'admin' => $total_admin,
        'custos' => $total_custos,
        'receita' => $receita_bruta,
        'lucro' => $lucro_real,
        'margem' => $margem_real
    ],
    'chart' => [
        'labels' => $chart_labels,
        'data' => $chart_data,
        'colors' => $chart_colors
    ]
];
