if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../PropostaRepository.php';

if (!isset($_SESSION['usuario_id'])) { 
    header("Location: login.php"); 
    exit; 
}

if (!isset($_GET['id'])) { 
    die("ID da proposta não fornecido."); 
}

$id_proposta = intval($_GET['id']);
$id_usuario = $_SESSION['usuario_id'];

try {
    $repo = new PropostaRepository();
    $dados_brutos = $repo->buscarPorId($id_proposta);

    if (!$dados_brutos || $dados_brutos['id_criador'] != $id_usuario) { 
        die("Proposta não encontrada ou acesso negado."); 
    }

    // Processamento de Custos Detalhados (Lógica de Cálculo para Relatório)
    $total_salarios = 0; $dados_salarios = [];
    foreach ($dados_brutos['itens']['salarios'] as $r) {
        $custo = ($r['quantidade'] * $r['salario_base'] * $r['fator_encargos'] / 30) * $r['dias'];
        $r['custo_calculado'] = $custo;
        $dados_salarios[] = $r;
        $total_salarios += $custo;
    }

    $total_estadia = 0; $dados_estadia = [];
    foreach ($dados_brutos['itens']['estadia'] as $r) {
        $custo = $r['quantidade'] * $r['valor_unitario'] * $r['dias'];
        $r['custo_calculado'] = $custo;
        $dados_estadia[] = $r;
        $total_estadia += $custo;
    }

    $total_consumos = 0; $dados_consumos = [];
    foreach ($dados_brutos['itens']['consumo'] as $r) {
        $kml = $r['consumo_kml'] > 0 ? $r['consumo_kml'] : 1;
        $custo = ($r['km_total'] * $r['valor_litro'] / $kml) * $r['quantidade'];
        $r['custo_calculado'] = $custo;
        $dados_consumos[] = $r;
        $total_consumos += $custo;
    }

    $total_locacao = 0; $dados_locacao = [];
    foreach ($dados_brutos['itens']['locacao'] as $r) {
        // Enriquecimento de nomes de equipamentos (se necessário, mas repository já deve lidar ou fazemos aqui)
        $custo = ($r['quantidade'] * $r['valor_mensal'] / 30) * $r['dias'];
        $r['custo_calculado'] = $custo;
        $dados_locacao[] = $r;
        $total_locacao += $custo;
    }

    $total_admin = 0; $dados_admin = [];
    foreach ($dados_brutos['itens']['admin'] as $r) {
        $custo = $r['quantidade'] * $r['valor'];
        $r['custo_calculado'] = $custo;
        $dados_admin[] = $r;
        $total_admin += $custo;
    }

    // Totais Finais
    $total_custos = $total_salarios + $total_estadia + $total_consumos + $total_locacao + $total_admin;
    $receita_bruta = $dados_brutos['valor_final_proposta'];
    $lucro_real = $receita_bruta - $total_custos;
    $margem_real = ($receita_bruta > 0) ? ($lucro_real / $receita_bruta) * 100 : 0;

    // Chart Data
    $chart_labels = [];
    $chart_data = [];
    $chart_colors = [];

    if ($total_salarios > 0) { $chart_labels[] = 'Equipe'; $chart_data[] = $total_salarios; $chart_colors[] = '#0ea5e9'; }
    if ($total_estadia > 0) { $chart_labels[] = 'Estadia'; $chart_data[] = $total_estadia; $chart_colors[] = '#8b5cf6'; }
    if ($total_consumos > 0) { $chart_labels[] = 'Combustível'; $chart_data[] = $total_consumos; $chart_colors[] = '#f97316'; }
    if ($total_locacao > 0) { $chart_labels[] = 'Equipamentos'; $chart_data[] = $total_locacao; $chart_colors[] = '#eab308'; }
    if ($total_admin > 0) { $chart_labels[] = 'Admin'; $chart_data[] = $total_admin; $chart_colors[] = '#64748b'; }

    return [
        'proposta' => $dados_brutos,
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

} catch (Exception $e) {
    die("Erro ao gerar relatório: " . $e->getMessage());
}
