<?php
/**
 * DASHBOARD DE TIPOS DE SERVIÇO - GEOMETRPOLE v2.0
 * 
 * Integra:
 * - Filtros avançados
 * - Gráficos de distribuição
 * - Sugestão automática
 * - Listagem de propostas
 * - Estatísticas em tempo real
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';

session_start();
// Autenticação simples (ajuste conforme seu sistema de login)
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$db = new Database();

// ============================================
// DADOS PARA FILTROS
// ============================================

$tiposServico = $db->fetchAll("
    SELECT id, nome, cor, icone, 
           (SELECT COUNT(*) FROM Propostas WHERE tipo_servico_id = tipos_servico.id) as total
    FROM tipos_servico 
    WHERE ativo = 1 
    ORDER BY ordem ASC
");

$periodos = [
    'todos' => 'Todo o período',
    'hoje' => 'Hoje',
    'semana' => 'Esta semana',
    'mes' => 'Este mês',
    'trimestre' => 'Este trimestre',
    'ano' => 'Este ano',
    'custom' => 'Personalizado'
];

$statusList = [
    'Rascunho' => ['label' => 'Rascunho', 'cor' => '#95a5a6'],
    'Enviada' => ['label' => 'Enviada', 'cor' => '#3498db'],
    'Aceita' => ['label' => 'Aceita', 'cor' => '#27ae60'],
    'Rejeitada' => ['label' => 'Rejeitada', 'cor' => '#e74c3c'],
    'Concluida' => ['label' => 'Concluída', 'cor' => '#9b59b6']
];

// ============================================
// APLICAR FILTROS
// ============================================

$filtrosAtivos = [];
$where = ["1=1"];
$params = [];

// Filtro por tipos
if (!empty($_GET['tipos'])) {
    $tipos = array_map('intval', $_GET['tipos']);
    $placeholders = implode(',', array_fill(0, count($tipos), '?'));
    $where[] = "p.tipo_servico_id IN ($placeholders)";
    $params = array_merge($params, $tipos);
    $filtrosAtivos[] = count($tipos) . ' tipo(s)';
}

// Filtro por período
if (!empty($_GET['periodo']) && $_GET['periodo'] !== 'todos') {
    switch ($_GET['periodo']) {
        case 'hoje':
            $where[] = "DATE(p.data_criacao) = CURDATE()";
            break;
        case 'semana':
            $where[] = "YEARWEEK(p.data_criacao) = YEARWEEK(CURDATE())";
            break;
        case 'mes':
            $where[] = "MONTH(p.data_criacao) = MONTH(CURDATE()) AND YEAR(p.data_criacao) = YEAR(CURDATE())";
            break;
        case 'trimestre':
            $where[] = "QUARTER(p.data_criacao) = QUARTER(CURDATE()) AND YEAR(p.data_criacao) = YEAR(CURDATE())";
            break;
        case 'ano':
            $where[] = "YEAR(p.data_criacao) = YEAR(CURDATE())";
            break;
        case 'custom':
            if (!empty($_GET['data_inicio']) && !empty($_GET['data_fim'])) {
                $where[] = "DATE(p.data_criacao) BETWEEN ? AND ?";
                $params[] = $_GET['data_inicio'];
                $params[] = $_GET['data_fim'];
            }
            break;
    }
    if ($_GET['periodo'] !== 'custom') {
        $filtrosAtivos[] = $periodos[$_GET['periodo']];
    }
}

// Filtro por status
if (!empty($_GET['status'])) {
    $status = $_GET['status'];
    $placeholders = implode(',', array_fill(0, count($status), '?'));
    $where[] = "p.status IN ($placeholders)";
    $params = array_merge($params, $status);
    $filtrosAtivos[] = count($status) . ' status';
}

// Filtro por valor
if (!empty($_GET['valor_min'])) {
    $where[] = "p.valor_final_proposta >= ?";
    $params[] = floatval($_GET['valor_min']);
}
if (!empty($_GET['valor_max'])) {
    $where[] = "p.valor_final_proposta <= ?";
    $params[] = floatval($_GET['valor_max']);
}

// Busca por texto
if (!empty($_GET['busca'])) {
    $where[] = "(ts.nome LIKE ? OR c.nome_cliente LIKE ? OR p.id_proposta LIKE ?)"; // Ajustado campos para esquema real
    $busca = '%' . $_GET['busca'] . '%';
    $params = array_merge($params, [$busca, $busca, $busca]);
    $filtrosAtivos[] = 'busca: "' . htmlspecialchars($_GET['busca']) . '"';
}

$sqlWhere = implode(' AND ', $where);

// ============================================
// EXPORTAÇÃO CSV/EXCEL
// ============================================

if (!empty($_GET['exportar'])) {
    $formato = $_GET['exportar']; // 'csv' ou 'excel'
    
    $filename = 'propostas_' . date('Y-m-d_H-i-s');
    
    // Headers para download
    if ($formato === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename={$filename}.csv");
        
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8 (Excel)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeçalhos
        fputcsv($output, ['Nº Proposta', 'Data', 'Cliente', 'Tipo de Serviço', 'Serviço', 'Valor', 'Status']);
        
        // Dados
        // Preciso buscar TODAS as propostas para exportação, não só as 50 da página
        // Reutilizar a query de listagem mas sem LIMIT
        $propostasExport = $db->fetchAll("
            SELECT 
                p.id_proposta, p.num_proposta as numero_proposta, p.valor_final_proposta as valor_total, p.status, p.data_criacao as created_at,
                t.nome as tipo_nome,
                c.nome_cliente as cliente_nome,
                p.servico
            FROM Propostas p
            LEFT JOIN tipos_servico t ON p.tipo_servico_id = t.id
            LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente
            WHERE $sqlWhere
            ORDER BY p.data_criacao DESC
        ", $params);

        foreach ($propostasExport as $prop) {
            fputcsv($output, [
                $prop['numero_proposta'],
                date('d/m/Y', strtotime($prop['created_at'])),
                $prop['cliente_nome'],
                $prop['tipo_nome'] ?? 'Não classificado',
                $prop['servico'],
                $prop['valor_total'],
                $prop['status']
            ]);
        }
        
        fclose($output);
        exit;
        
    } elseif ($formato === 'excel') {
        // Recarregar dados para exportação completa
         $propostasExport = $db->fetchAll("
            SELECT 
                p.id_proposta, p.num_proposta as numero_proposta, p.valor_final_proposta as valor_total, p.status, p.data_criacao as created_at,
                t.nome as tipo_nome, t.cor as tipo_cor,
                c.nome_cliente as cliente_nome,
                p.servico
            FROM Propostas p
            LEFT JOIN tipos_servico t ON p.tipo_servico_id = t.id
            LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente
            WHERE $sqlWhere
            ORDER BY p.data_criacao DESC
        ", $params);

        // HTML formatado como Excel
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header("Content-Disposition: attachment; filename={$filename}.xls");
        
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
        echo '<head><meta charset="utf-8"></head><body>';
        echo '<table border="1">';
        
        // Cabeçalhos com estilo
        echo '<tr style="background:#2c3e50;color:white;font-weight:bold;">';
        echo '<th>Nº Proposta</th><th>Data</th><th>Cliente</th><th>Tipo</th><th>Serviço</th><th>Valor</th><th>Status</th>';
        echo '</tr>';
        
        foreach ($propostasExport as $prop) {
            $corStatus = [
                'rascunho' => '#95a5a6',
                'enviada' => '#3498db',
                'aceita' => '#27ae60',
                'rejeitada' => '#e74c3c',
                'concluida' => '#9b59b6'
            ][$prop['status']] ?? '#95a5a6';
            
            echo '<tr>';
            echo '<td>' . $prop['numero_proposta'] . '</td>';
            echo '<td>' . date('d/m/Y', strtotime($prop['created_at'])) . '</td>';
            echo '<td>' . $prop['cliente_nome'] . '</td>';
            echo '<td style="background:' . ($prop['tipo_cor'] ?? '#95a5a6') . ';color:white;">' . ($prop['tipo_nome'] ?? '-') . '</td>';
            echo '<td>' . substr($prop['servico'], 0, 50) . '...</td>';
            echo '<td style="text-align:right;">R$ ' . number_format($prop['valor_total'], 2, ',', '.') . '</td>';
            echo '<td style="background:' . $corStatus . ';color:white;text-align:center;">' . ucfirst($prop['status']) . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
        echo '</body></html>';
        exit;
    }
}

// ============================================
// ESTATÍSTICAS GERAIS
// ============================================

$stats = $db->fetch("
    SELECT 
        COUNT(*) as total_propostas,
        SUM(CASE WHEN p.status = 'Aceita' THEN 1 ELSE 0 END) as propostas_aceitas,
        SUM(p.valor_final_proposta) as valor_total,
        AVG(p.valor_final_proposta) as ticket_medio
    FROM Propostas p
    LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente
    LEFT JOIN tipos_servico ts ON p.tipo_servico_id = ts.id
    WHERE $sqlWhere
", $params);

// ============================================
// DADOS POR TIPO (PARA GRÁFICOS)
// ============================================

$dadosPorTipo = $db->fetchAll("
    SELECT 
        t.id,
        t.nome,
        t.cor,
        t.icone,
        COUNT(p.id_proposta) as quantidade,
        SUM(p.valor_final_proposta) as valor_total,
        AVG(p.valor_final_proposta) as ticket_medio,
        ROUND(COUNT(p.id_proposta) * 100.0 / NULLIF((SELECT COUNT(*) FROM Propostas p2 
            LEFT JOIN Clientes c ON p2.id_cliente = c.id_cliente 
            LEFT JOIN tipos_servico ts ON p2.tipo_servico_id = ts.id
            WHERE $sqlWhere), 0), 1) as percentual
    FROM tipos_servico t
    LEFT JOIN Propostas p ON t.id = p.tipo_servico_id 
    LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente
    WHERE t.ativo = 1 AND ($sqlWhere)
    GROUP BY t.id
    HAVING quantidade > 0 OR t.id IN (" . implode(',', array_map('intval', $_GET['tipos'] ?? [0])) . ")
    ORDER BY quantidade DESC
", array_merge($params, $params)); // Duplicado params pq subquery usa where tb? Não, subquery params precisam ser passados? Onde?
// A subquery SELECT COUNT(*) FROM Propostas p2 ... WHERE $sqlWhere precisa dos parametros também.
// Então params precisa ser duplicado se a subquery usar os mesmos parametros.
// No código acima, a subquery SELECT COUNT(*) FROM Propostas p2 WHERE $sqlWhere ESTÁ CONCATENADA na string, mas os placeholders ? precisam de valores.
// Sim, $sqlWhere tem placeholders. A query principal tem $sqlWhere (uso 1) e a subquery tem $sqlWhere (uso 2).
// Preciso duplicar params.
// Mas espera, $dadosPorTipo usa LEFT JOIN Propostas p ... WHERE ... ($sqlWhere).
// A subquery está dentro do SELECT field list.

// CORREÇÃO: $db->fetchAll não suporta named parameters ou reutilização fácil de ? posicional.
// Vou simplificar a query para evitar erro de contagem de parametros.
// Farei o total filtrado separadamente.

$totalFiltrado = $stats['total_propostas'] > 0 ? $stats['total_propostas'] : 1;

$dadosPorTipo = $db->fetchAll("
    SELECT 
        t.id,
        t.nome,
        t.cor,
        t.icone,
        COUNT(p.id_proposta) as quantidade,
        SUM(p.valor_final_proposta) as valor_total,
        AVG(p.valor_final_proposta) as ticket_medio,
        ROUND(COUNT(p.id_proposta) * 100.0 / ?, 1) as percentual
    FROM tipos_servico t
    LEFT JOIN Propostas p ON t.id = p.tipo_servico_id 
    LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente
    WHERE t.ativo = 1 AND ($sqlWhere)
    GROUP BY t.id
    HAVING quantidade > 0
    ORDER BY quantidade DESC
", array_merge([$totalFiltrado], $params));


// ============================================
// LISTA DE PROPOSTAS
// ============================================

$propostas = $db->fetchAll("
    SELECT 
        p.id_proposta, p.num_proposta, p.valor_final_proposta, p.status, p.data_criacao,
        t.nome as tipo_nome,
        t.cor as tipo_cor,
        t.icone as tipo_icone,
        c.nome_cliente as cliente_nome
    FROM Propostas p
    LEFT JOIN tipos_servico t ON p.tipo_servico_id = t.id
    LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente
    WHERE $sqlWhere
    ORDER BY p.data_criacao DESC
    LIMIT 50
", $params);

// ============================================
// EVOLUÇÃO MENSAL (ÚLTIMOS 6 MESES)
// ============================================

$evolucaoMensal = $db->fetchAll("
    SELECT 
        DATE_FORMAT(p.data_criacao, '%Y-%m') as mes,
        DATE_FORMAT(p.data_criacao, '%b/%Y') as mes_label,
        COUNT(*) as total,
        SUM(p.valor_final_proposta) as valor
    FROM Propostas p
    WHERE p.data_criacao >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY mes
    ORDER BY mes ASC
");

function formatarMoeda($valor) {
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}

function formatarData($data) {
    return date('d/m/Y', strtotime($data));
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Tipos - GEOMETRPOLE</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --cor-primaria: #2c3e50; --cor-secundaria: #34495e; --cor-destaque: #3498db; --cor-sucesso: #27ae60; --sombra: 0 2px 8px rgba(0,0,0,0.08); }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f5f7fa; color: #333; line-height: 1.6; }
        .dashboard-container { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }
        .sidebar { background: var(--cor-primaria); color: white; padding: 25px 20px; position: fixed; height: 100vh; width: 260px; overflow-y: auto; }
        .sidebar-logo { font-size: 22px; font-weight: 700; margin-bottom: 30px; display: flex; align-items: center; gap: 10px; }
        .nav-link { display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: rgba(255,255,255,0.75); text-decoration: none; border-radius: 8px; transition: all 0.2s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: white; }
        .main-content { margin-left: 260px; padding: 25px 30px; }
        .page-header { margin-bottom: 25px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: var(--sombra); display: flex; align-items: center; gap: 15px; }
        .stat-icon { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
        .blue { background: #e3f2fd; color: #1976d2; } .green { background: #e8f5e9; color: #388e3c; } .orange { background: #fff3e0; color: #f57c00; } .purple { background: #f3e5f5; color: #7b1fa2; }
        .filtro-panel { background: white; border-radius: 12px; box-shadow: var(--sombra); margin-bottom: 25px; overflow: hidden; }
        .filtro-header { padding: 15px 20px; background: #f8f9fa; cursor: pointer; display: flex; justify-content: space-between; align-items: center; }
        .filtro-body { padding: 20px; display: none; }
        .filtro-body.active { display: block; }
        .graficos-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px; }
        .grafico-card { background: white; border-radius: 12px; box-shadow: var(--sombra); padding: 20px; }
        .pizza-container { position: relative; height: 300px; display: flex; justify-content: center; }
        .pizza-center { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; font-size: 24px; font-weight: bold; }
        @media (max-width: 768px) { .dashboard-container { grid-template-columns: 1fr; } .sidebar { display: none; } .main-content { margin-left: 0; } .graficos-grid { grid-template-columns: 1fr; } }
        
        /* Table styles */
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px; background: #f8f9fa; border-bottom: 2px solid #ddd; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; color: white; display: inline-block;}
        .tipo-chip { display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; border: 1px solid #ddd; border-radius: 15px; cursor: pointer; margin-right: 5px; margin-bottom: 5px; }
        .tipo-chip input { display: none; }
        .tipo-chip:has(input:checked) { background: #e3f2fd; border-color: #2196f3; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-logo"><i class="fas fa-cube"></i> GEOMETRPOLE</div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="../painel.php" class="nav-link"><i class="fas fa-arrow-left"></i> Voltar ao CRM</a></li>
                <li class="nav-item"><a href="dashboard_tipos.php" class="nav-link active"><i class="fas fa-chart-pie"></i> Dashboard Tipos</a></li>
            </ul>
        </aside>
        
        <main class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-chart-pie"></i> Dashboard de Tipos de Serviço</h1>
            </div>
            
            <!-- STATS -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-file-alt"></i></div>
                    <div><h3><?php echo number_format($stats['total_propostas']); ?></h3><p>Total Propostas</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                    <div><h3><?php echo number_format($stats['propostas_aceitas']); ?></h3><p>Aceitas</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="fas fa-dollar-sign"></i></div>
                    <div><h3><?php echo formatarMoeda($stats['valor_total']); ?></h3><p>Valor Total</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="fas fa-receipt"></i></div>
                    <div><h3><?php echo formatarMoeda($stats['ticket_medio']); ?></h3><p>Ticket Médio</p></div>
                </div>
            </div>

            <!-- FILTROS -->
            <div class="filtro-panel">
                <div class="filtro-header" onclick="document.getElementById('filtroBody').classList.toggle('active')">
                    <h3><i class="fas fa-filter"></i> Filtros</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="filtro-body <?php echo !empty($filtrosAtivos) ? 'active' : ''; ?>" id="filtroBody">
                    <form method="GET">
                        <div style="margin-bottom:15px">
                            <label>Tipos:</label><br>
                            <?php foreach ($tiposServico as $tipo): ?>
                                <label class="tipo-chip">
                                    <input type="checkbox" name="tipos[]" value="<?php echo $tipo['id']; ?>" <?php echo in_array($tipo['id'], $_GET['tipos']??[])?'checked':''; ?>>
                                    <i class="fas fa-<?php echo $tipo['icone']; ?>"></i> <?php echo $tipo['nome']; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div style="display:flex; gap:10px; margin-bottom:15px">
                            <select name="periodo" class="form-control" style="padding:8px">
                                <?php foreach ($periodos as $k=>$v) echo "<option value='$k' ".($k==($_GET['periodo']??'')?'selected':'').">$v</option>"; ?>
                            </select>
                            <input type="text" name="busca" placeholder="Buscar..." value="<?php echo htmlspecialchars($_GET['busca']??''); ?>" style="padding:8px">
                            <button type="submit" class="btn btn-primary" style="background:#3498db; color:white; border:none; padding:8px 15px; border-radius:5px">Filtrar</button>
                            <a href="?" style="padding:8px 15px;">Limpar</a>
                        </div>
                    </form>
                </div>
            </div>

<!-- GRÁFICOS EXPANDIDOS -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:25px;margin-bottom:25px;">
    
    <!-- PIZZA (mantido) -->
    <div class="grafico-card">
        <div class="grafico-header">
            <h3><i class="fas fa-chart-pie"></i> Distribuição por Tipo</h3>
        </div>
        <div class="grafico-body">
            <?php if (empty($dadosPorTipo)): ?>
                <div class="empty-state"><i class="fas fa-chart-pie"></i><p>Sem dados</p></div>
            <?php else: ?>
                <div class="pizza-container" style="height:250px;">
                    <canvas id="graficoPizza"></canvas>
                    <div class="pizza-center">
                        <span class="pizza-total"><?php echo $stats['total_propostas']; ?></span>
                        <small>propostas</small>
                    </div>
                </div>
                <div class="pizza-legenda" style="max-height:150px;overflow-y:auto;margin-top:15px;">
                    <?php foreach ($dadosPorTipo as $item): ?>
                        <div class="legenda-item" onclick="filtrarPorTipo(<?php echo $item['id']; ?>)" style="display:flex;align-items:center;gap:10px;padding:5px;cursor:pointer;border-bottom:1px solid #f0f0f0;">
                            <span class="legenda-cor" style="width:12px;height:12px;border-radius:50%;background:<?php echo $item['cor']; ?>;display:inline-block;"></span>
                            <div class="legenda-info" style="display:flex;justify-content:space-between;width:100%;">
                                <span class="legenda-nome" style="font-size:12px;"><?php echo $item['nome']; ?></span>
                                <span class="legenda-dados" style="font-size:12px;">
                                    <strong><?php echo $item['quantidade']; ?></strong>
                                    (<?php echo $item['percentual']; ?>%)
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- BARRAS (mantido) -->
    <div class="grafico-card">
        <div class="grafico-header">
            <h3><i class="fas fa-chart-bar"></i> Valor por Tipo</h3>
        </div>
        <div class="grafico-body">
            <canvas id="graficoBarras" height="250"></canvas>
        </div>
    </div>
    
</div>

<!-- NOVO: LINHA DO TEMPO -->
<div class="grafico-card" style="margin-bottom:25px;">
    <div class="grafico-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
        <h3><i class="fas fa-chart-line"></i> Evolução Mensal</h3>
        <div style="display:flex;gap:10px;">
            <select id="metricaLinha" onchange="atualizarGraficoLinha()" style="padding:5px 10px;border:1px solid #ddd;border-radius:4px;font-size:12px;">
                <option value="quantidade">Quantidade</option>
                <option value="valor">Valor (R$)</option>
            </select>
        </div>
    </div>
    <div class="grafico-body">
        <canvas id="graficoLinha" height="100"></canvas>
    </div>
</div>

<!-- NOVO: COMPARATIVO ANO VS ANO -->
<div class="grafico-card" style="margin-bottom:25px;">
    <div class="grafico-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
        <h3><i class="fas fa-balance-scale"></i> Comparativo Ano vs Ano</h3>
        <div style="font-size:12px;color:#7f8c8d;">
            <?php echo date('Y') - 1; ?> vs <?php echo date('Y'); ?>
        </div>
    </div>
    <div class="grafico-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:20px;">
            
            <?php
            // Dados ano atual vs anterior
            $anoAtual = date('Y');
            $anoAnterior = $anoAtual - 1;
            
            $comparativo = $db->fetch("
                SELECT 
                    SUM(CASE WHEN YEAR(data_criacao) = ? THEN 1 ELSE 0 END) as propostas_atual,
                    SUM(CASE WHEN YEAR(data_criacao) = ? THEN 1 ELSE 0 END) as propostas_anterior,
                    SUM(CASE WHEN YEAR(data_criacao) = ? THEN valor_final_proposta ELSE 0 END) as valor_atual,
                    SUM(CASE WHEN YEAR(data_criacao) = ? THEN valor_final_proposta ELSE 0 END) as valor_anterior
                FROM Propostas
                WHERE YEAR(data_criacao) IN (?, ?)
            ", [$anoAtual, $anoAnterior, $anoAtual, $anoAnterior, $anoAtual, $anoAnterior]);
            
            $crescimentoPropostas = $comparativo['propostas_anterior'] > 0 
                ? (($comparativo['propostas_atual'] - $comparativo['propostas_anterior']) / $comparativo['propostas_anterior'] * 100)
                : 0;
                
            $crescimentoValor = $comparativo['valor_anterior'] > 0
                ? (($comparativo['valor_atual'] - $comparativo['valor_anterior']) / $comparativo['valor_anterior'] * 100)
                : 0;
            ?>
            
            <div style="text-align:center;padding:20px;background:#f8f9fa;border-radius:8px;">
                <div style="font-size:32px;font-weight:700;color:<?php echo $crescimentoPropostas >= 0 ? '#27ae60' : '#e74c3c'; ?>">
                    <?php echo ($crescimentoPropostas >= 0 ? '+' : '') . number_format($crescimentoPropostas, 1); ?>%
                </div>
                <div style="color:#7f8c8d;font-size:13px;">Propostas</div>
                <div style="font-size:11px;color:#999;margin-top:5px;">
                    <?php echo $comparativo['propostas_anterior']; ?> → <?php echo $comparativo['propostas_atual']; ?>
                </div>
            </div>
            
            <div style="text-align:center;padding:20px;background:#f8f9fa;border-radius:8px;">
                <div style="font-size:32px;font-weight:700;color:<?php echo $crescimentoValor >= 0 ? '#27ae60' : '#e74c3c'; ?>">
                    <?php echo ($crescimentoValor >= 0 ? '+' : '') . number_format($crescimentoValor, 1); ?>%
                </div>
                <div style="color:#7f8c8d;font-size:13px;">Faturamento</div>
                <div style="font-size:11px;color:#999;margin-top:5px;">
                    <?php echo formatarMoeda($comparativo['valor_anterior']); ?> → <?php echo formatarMoeda($comparativo['valor_atual']); ?>
                </div>
            </div>
            
        </div>
        <canvas id="graficoComparativo" height="80"></canvas>
    </div>
</div>

            <!-- TABELA -->
            <!-- LISTA DE PROPOSTAS COM EXPORTAÇÃO -->
            <div class="grafico-card" class="propostas-section">
                <div class="section-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
                    <h3><i class="fas fa-list"></i> Propostas (<?php echo count($propostas); ?>)</h3>
                    <div style="display:flex;gap:10px;">
                        
                        <!-- DROPDOWN DE EXPORTAÇÃO -->
                        <div style="position:relative;">
                            <button class="btn btn-secondary btn-sm" onclick="toggleExportMenu()" id="btnExport" style="padding:8px 12px; border:1px solid #ccc; background:white; border-radius:5px; cursor:pointer;">
                                <i class="fas fa-download"></i> Exportar <i class="fas fa-chevron-down" style="margin-left:5px;"></i>
                            </button>
                            <div id="exportMenu" style="display:none;position:absolute;right:0;top:100%;margin-top:5px;background:white;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);min-width:150px;z-index:100;">
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['exportar' => 'csv'])); ?>" 
                                   style="display:flex;align-items:center;gap:8px;padding:12px 15px;color:#333;text-decoration:none;font-size:13px;border-bottom:1px solid #f0f0f0;"
                                   onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='white'">
                                    <i class="fas fa-file-csv" style="color:#27ae60;"></i> CSV
                                </a>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['exportar' => 'excel'])); ?>" 
                                   style="display:flex;align-items:center;gap:8px;padding:12px 15px;color:#333;text-decoration:none;font-size:13px;"
                                   onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='white'">
                                    <i class="fas fa-file-excel" style="color:#217346;"></i> Excel
                                </a>
                            </div>
                        </div>
                        
                        <a href="../editor_dinamico.php?acao=novo" class="btn btn-success btn-sm" style="padding:8px 12px; background:#27ae60; color:white; text-decoration:none; border-radius:5px;">
                            <i class="fas fa-plus"></i> Nova Proposta
                        </a>
                    </div>
                </div>
                <table>
                    <thead><tr><th>#</th><th>Cliente</th><th>Tipo</th><th>Valor</th><th>Status</th><th>Ações</th></tr></thead>
                    <tbody>
                        <?php foreach($propostas as $proposta): ?>
                        <tr>
                            <td><?php echo $proposta['num_proposta']; ?></td>
                            <td><?php echo $proposta['cliente_nome']; ?></td>
                            <td>
                                <?php if($proposta['tipo_nome']): ?>
                                <span style="background:<?php echo $proposta['tipo_cor']; ?>; color:white; padding:3px 8px; border-radius:10px; font-size:11px">
                                    <i class="fas fa-<?php echo $proposta['tipo_icone']; ?>"></i> <?php echo $proposta['tipo_nome']; ?>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatarMoeda($proposta['valor_final_proposta']); ?></td>
                            <td><?php echo $proposta['status']; ?></td>
                            <td><a href="../editor_dinamico.php?id=<?php echo $proposta['id_proposta']; ?>&acao=visualizar"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

    <script>
    // Toggle filtro
    function toggleFiltro() {
        document.querySelector('.filtro-header').classList.toggle('active');
        document.getElementById('filtroBody').classList.toggle('active');
    }

    // Toggle menu exportação
    function toggleExportMenu() {
        const menu = document.getElementById('exportMenu');
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
    }

    // Fechar menu ao clicar fora
    document.addEventListener('click', function(e) {
        const btn = document.getElementById('btnExport');
        const menu = document.getElementById('exportMenu');
        if (btn && menu && !btn.contains(e.target) && !menu.contains(e.target)) {
            menu.style.display = 'none';
        }
    });

    // Filtrar por tipo ao clicar na legenda
    function filtrarPorTipo(tipoId) {
        const checkbox = document.querySelector(`input[value="${tipoId}"]`);
        if (checkbox) {
            checkbox.checked = !checkbox.checked;
            document.forms[0].submit();
        }
    }

    // Dados para gráficos
    const dadosPorTipo = <?php echo json_encode($dadosPorTipo); ?>;
    const evolucaoMensal = <?php echo json_encode($evolucaoMensal); ?>;
    const comparativoAno = <?php echo json_encode([
        'atual' => $comparativo['propostas_atual'] ?? 0,
        'anterior' => $comparativo['propostas_anterior'] ?? 0,
        'valor_atual' => $comparativo['valor_atual'] ?? 0,
        'valor_anterior' => $comparativo['valor_anterior'] ?? 0
    ]); ?>;

    // GRÁFICO DE PIZZA
    if (dadosPorTipo.length > 0) {
        const ctxPizza = document.getElementById('graficoPizza').getContext('2d');
        new Chart(ctxPizza, {
            type: 'doughnut',
            data: {
                labels: dadosPorTipo.map(d => d.nome),
                datasets: [{
                    data: dadosPorTipo.map(d => d.quantidade),
                    backgroundColor: dadosPorTipo.map(d => d.cor),
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const d = dadosPorTipo[context.dataIndex];
                                return `${d.nome}: ${d.quantidade} (${d.percentual}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // GRÁFICO DE BARRAS
    if (dadosPorTipo.length > 0) {
        const ctxBarras = document.getElementById('graficoBarras').getContext('2d');
        new Chart(ctxBarras, {
            type: 'bar',
            data: {
                labels: dadosPorTipo.map(d => d.nome.substring(0, 15) + (d.nome.length > 15 ? '...' : '')),
                datasets: [{
                    label: 'Valor Total',
                    data: dadosPorTipo.map(d => d.valor_total),
                    backgroundColor: dadosPorTipo.map(d => d.cor),
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + (value / 1000).toFixed(0) + 'k';
                            }
                        }
                    }
                }
            }
        });
    }

    // GRÁFICO DE LINHA - EVOLUÇÃO MENSAL
    if (document.getElementById('graficoLinha')) {
        const ctxLinha = document.getElementById('graficoLinha').getContext('2d');
        let graficoLinha;

        function criarGraficoLinha(metrica = 'quantidade') {
            const dados = evolucaoMensal;
            const label = metrica === 'quantidade' ? 'Quantidade de Propostas' : 'Valor Total (R$)';
            const data = metrica === 'quantidade' ? dados.map(d => d.total) : dados.map(d => d.valor);
            
            if (graficoLinha) graficoLinha.destroy();
            
            graficoLinha = new Chart(ctxLinha, {
                type: 'line',
                data: {
                    labels: dados.map(d => d.mes_label),
                    datasets: [{
                        label: label,
                        data: data,
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true, position: 'top' }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return metrica === 'valor' ? 'R$ ' + (value / 1000).toFixed(0) + 'k' : value;
                                }
                            }
                        }
                    }
                }
            });
        }

        window.atualizarGraficoLinha = function() {
            const metrica = document.getElementById('metricaLinha').value;
            criarGraficoLinha(metrica);
        }

        // Inicializar gráfico de linha
        if (evolucaoMensal.length > 0) {
            criarGraficoLinha('quantidade');
        }
    }

    // GRÁFICO COMPARATIVO ANO VS ANO
    if (document.getElementById('graficoComparativo')) {
        const ctxComp = document.getElementById('graficoComparativo').getContext('2d');
        new Chart(ctxComp, {
            type: 'bar',
            data: {
                labels: ['<?php echo $anoAnterior; ?>', '<?php echo $anoAtual; ?>'],
                datasets: [
                    {
                        label: 'Propostas',
                        data: [comparativoAno.anterior, comparativoAno.atual],
                        backgroundColor: '#95a5a6',
                        borderRadius: 6
                    },
                    {
                        label: 'Valor Total (mil R$)',
                        data: [
                            (comparativoAno.valor_anterior / 1000).toFixed(0), 
                            (comparativoAno.valor_atual / 1000).toFixed(0)
                        ],
                        backgroundColor: '#3498db',
                        borderRadius: 6,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Propostas' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: { display: true, text: 'Valor (mil R$)' },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });
    }

    // Abrir filtro automaticamente se houver filtros
    <?php if (!empty($filtrosAtivos)): ?>
        document.addEventListener('DOMContentLoaded', toggleFiltro);
    <?php endif; ?>
    </script>
</body>
</html>
