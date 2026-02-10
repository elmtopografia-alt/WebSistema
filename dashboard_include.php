<?php
// ARQUIVO: dashboard_include.php

// GARANTIR ID DO CRIADOR (SEGURANÇA SAAS)
$id_logado_dash = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : (isset($_SESSION['id_criador']) ? $_SESSION['id_criador'] : 0);

// =========================================================
// 0. AUTO-MIGRAÇÃO (SELF-HEALING) - CRM LEAN
// =========================================================
// Verifica se as colunas existem. Se não, aplica o ALTER TABLE silenciosamente.
try {
    // Tenta selecionar uma das novas colunas para testar
    $check_crm = $conn->query("SELECT status_crm FROM Propostas LIMIT 1");
    if (!$check_crm) {
        // Coluna não existe, rodar migração
        $queries_migracao = [
            "ALTER TABLE Propostas ADD COLUMN alert_crm tinyint(1) DEFAULT 0",
            "ALTER TABLE Propostas ADD COLUMN data_ultimo_contato DATETIME DEFAULT CURRENT_TIMESTAMP",
            "ALTER TABLE Propostas ADD COLUMN previsao_pagamento DATE DEFAULT NULL",
            "ALTER TABLE Propostas ADD COLUMN status_crm varchar(50) DEFAULT 'Novo'",
            "ALTER TABLE Propostas ADD COLUMN motivo_perda TEXT DEFAULT NULL",
            "ALTER TABLE Propostas ADD COLUMN valor_recebido DECIMAL(10,2) DEFAULT 0.00",
            // Updates de sanidade (evitar NULL)
            "UPDATE Propostas SET data_ultimo_contato = data_criacao WHERE data_ultimo_contato IS NULL",
            "UPDATE Propostas SET status_crm = 'Novo' WHERE status_crm IS NULL"
        ];

        foreach ($queries_migracao as $q) {
            try { $conn->query($q); } catch (Exception $e) { /* Ignora erro se coluna já existe entre comandos */ }
        }
    }
} catch (Exception $e) {
    // Falha silenciosa para não quebrar o painel inteiro
    error_log("Erro Auto-Migração CRM: " . $e->getMessage());
}

// =========================================================
// 1. KPIS FINANCEIROS REAIS
// =========================================================
$sql_kpis = "SELECT 
    SUM(CASE WHEN status = 'Aprovada' THEN valor_final_proposta ELSE 0 END) as total_vendido,
    SUM(valor_recebido) as total_recebido,
    SUM(CASE WHEN status = 'Enviada' THEN valor_final_proposta ELSE 0 END) as pipeline_aberto,
    COUNT(CASE WHEN status = 'Enviada' THEN 1 END) as qtd_negociacao
FROM Propostas 
WHERE id_criador = $id_logado_dash";

$res_kpis = $conn->query($sql_kpis);
$kpi_data = $res_kpis ? $res_kpis->fetch_assoc() : ['total_vendido'=>0, 'total_recebido'=>0, 'pipeline_aberto'=>0, 'qtd_negociacao'=>0];

// Cálculo: A Receber = Vendido - Recebido (sem negativos)
$kpi_a_receber = max(0, $kpi_data['total_vendido'] - $kpi_data['total_recebido']);

// =========================================================
// 2. RADAR DE ATENÇÃO (FOLLOW-UP)
// Critério: Status 'Enviada' E (Alert=1 OU > 3 dias sem contato)
// =========================================================
$sql_radar = "SELECT 
                id_proposta, 
                numero_proposta, 
                nome_cliente_salvo, 
                data_ultimo_contato, 
                DATEDIFF(NOW(), data_ultimo_contato) as dias_sem_contato 
              FROM Propostas 
              WHERE id_criador = $id_logado_dash 
              AND status = 'Enviada' 
              AND (alert_crm = 1 OR data_ultimo_contato < DATE_SUB(NOW(), INTERVAL 3 DAY))
              ORDER BY dias_sem_contato DESC 
              LIMIT 5";

$res_radar = $conn->query($sql_radar);
$radar_items = [];
if ($res_radar) {
    while($r = $res_radar->fetch_assoc()) {
        $radar_items[] = $r;
    }
}

// =========================================================
// 3. GRÁFICO DE EVOLUÇÃO (LINHA)
// =========================================================
$sql_linha = "SELECT 
            DATE_FORMAT(data_criacao, '%m/%Y') as mes_ano,
            SUM(CASE WHEN status = 'Em elaboração' THEN valor_final_proposta ELSE 0 END) as elaboracao,
            SUM(CASE WHEN status = 'Enviada' THEN valor_final_proposta ELSE 0 END) as enviada,
            SUM(CASE WHEN status = 'Aprovada' THEN valor_final_proposta ELSE 0 END) as aprovada,
            SUM(CASE WHEN status = 'Cancelada' THEN valor_final_proposta ELSE 0 END) as cancelada
        FROM Propostas 
        WHERE id_criador = $id_logado_dash
        AND data_criacao >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(data_criacao, '%Y-%m') 
        ORDER BY data_criacao ASC";

$res_linha = $conn->query($sql_linha);
$meses = []; $elab = []; $env = []; $aprov = []; $canc = [];
if ($res_linha) {
    while($row = $res_linha->fetch_assoc()) {
        $meses[] = $row['mes_ano'];
        $elab[]  = $row['elaboracao'];
        $env[]   = $row['enviada'];
        $aprov[] = $row['aprovada'];
        $canc[]  = $row['cancelada'];
    }
}

// =========================================================
// 4. GRÁFICO DE PIZZA (STATUS)
// =========================================================
$sql_pizza = "SELECT status, COUNT(*) as total 
              FROM Propostas 
              WHERE id_criador = $id_logado_dash 
              GROUP BY status";
$res_pizza = $conn->query($sql_pizza);
$pizza_labels = []; $pizza_valores = [];
if ($res_pizza) {
    while($row = $res_pizza->fetch_assoc()) {
        $pizza_labels[] = $row['status'];
        $pizza_valores[] = $row['total'];
    }
}
?>

<!-- === [UI START] === -->

<!-- KPIS FINANCEIROS -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <!-- Card: Caixa Real -->
    <div class="glass-panel p-5 rounded-2xl border-l-4 border-emerald-500 bg-gradient-to-br from-emerald-900/20 to-transparent">
        <div class="text-emerald-400 text-xs uppercase font-bold tracking-wider mb-1 flex justify-between">
            <span>Em Caixa (Real)</span>
            <i class="ph ph-wallet text-lg"></i>
        </div>
        <div class="text-2xl font-display font-bold text-white">
            R$ <?php echo number_format($kpi_data['total_recebido'], 2, ',', '.'); ?>
        </div>
        <div class="text-xs text-slate-500 mt-2">Pagamentos confirmados</div>
    </div>

    <!-- Card: A Receber -->
    <div class="glass-panel p-5 rounded-2xl border-l-4 border-blue-500">
        <div class="text-blue-400 text-xs uppercase font-bold tracking-wider mb-1 flex justify-between">
            <span>A Receber</span>
            <i class="ph ph-hand-coins text-lg"></i>
        </div>
        <div class="text-2xl font-display font-bold text-white">
            R$ <?php echo number_format($kpi_a_receber, 2, ',', '.'); ?>
        </div>
        <div class="text-xs text-slate-500 mt-2">Contratos fechados pendentes</div>
    </div>

    <!-- Card: Pipeline -->
    <div class="glass-panel p-5 rounded-2xl border-l-4 border-yellow-500">
        <div class="text-yellow-400 text-xs uppercase font-bold tracking-wider mb-1 flex justify-between">
            <span>Pipeline (Na Mesa)</span>
            <i class="ph ph-briefcase text-lg"></i>
        </div>
        <div class="text-2xl font-display font-bold text-white">
            R$ <?php echo number_format($kpi_data['pipeline_aberto'], 2, ',', '.'); ?>
        </div>
        <div class="text-xs text-slate-500 mt-2"><?php echo $kpi_data['qtd_negociacao']; ?> propostas em negociação</div>
    </div>
    
    <!-- Card: Radar -->
    <div class="glass-panel p-5 rounded-2xl border-l-4 border-red-500 relative overflow-hidden group hover:bg-red-900/10 transition-colors">
        <div class="text-red-400 text-xs uppercase font-bold tracking-wider mb-1 flex justify-between">
            <span>Atenção Necessária</span>
            <i class="ph ph-siren text-lg animate-pulse"></i>
        </div>
        <div class="text-2xl font-display font-bold text-white">
            <?php echo count($radar_items); ?>
        </div>
        <div class="text-xs text-slate-500 mt-2 group-hover:text-red-300 transition-colors">Clientes esfriando (>3 dias)</div>
    </div>
</div>

<!-- RADAR DE ATENÇÃO (Se houver itens) -->
<?php if (count($radar_items) > 0): ?>
<div class="mb-8" id="crm-radar-section">
    <div class="glass-panel rounded-2xl overflow-hidden border border-red-500/30">
        <div class="bg-red-500/10 px-6 py-3 border-b border-red-500/20 flex justify-between items-center">
            <h5 class="text-red-400 font-bold flex items-center gap-2 text-sm">
                <i class="ph ph-target"></i> Radar de Follow-up
            </h5>
            <span class="text-[10px] uppercase font-bold text-red-300 bg-red-500/20 px-2 py-1 rounded">Prioridade Alta</span>
        </div>
        <div class="p-0">
            <table class="w-full text-left text-sm">
                <tbody class="divide-y divide-white/5">
                    <?php foreach($radar_items as $item): ?>
                    <tr class="hover:bg-white/5 transition-colors" id="radar-row-<?php echo $item['id_proposta']; ?>">
                        <td class="px-6 py-3 font-medium text-white">
                            <?php echo $item['nome_cliente_salvo']; ?>
                            <div class="text-xs text-slate-500">Proposta #<?php echo $item['numero_proposta']; ?></div>
                        </td>
                        <td class="px-6 py-3 text-slate-400">
                            <span class="inline-flex items-center gap-1 text-red-400">
                                <i class="ph ph-clock"></i> <?php echo $item['dias_sem_contato']; ?> dias sem contato
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <button onclick="crmRegistrarTouch(<?php echo $item['id_proposta']; ?>)" 
                                    class="bg-brand-accent/10 hover:bg-brand-accent text-brand-accent hover:text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all border border-brand-accent/30 flex items-center gap-2 float-right group">
                                <i class="ph ph-whatsapp-logo text-base group-hover:scale-110 transition-transform"></i> 
                                Já Cobrei
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- GRÁFICOS (Layout Original Preservado e Enriquecido) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    
    <!-- Gráfico de Linha (2/3) -->
    <div class="md:col-span-2 glass-panel rounded-2xl p-6 flex flex-col h-full">
        <div class="flex justify-between items-center mb-6">
            <h5 class="font-display text-lg font-bold text-white flex items-center gap-2">
                <i class="ph ph-chart-line-up text-brand-accent"></i> Evolução Financeira
            </h5>
            <span class="px-3 py-1 rounded-full bg-white/5 text-slate-400 text-xs font-bold border border-white/10">
                Últimos 6 meses
            </span>
        </div>
        <div class="flex-1 relative min-h-[300px]">
            <canvas id="chartLinha"></canvas>
        </div>
    </div>

    <!-- Gráfico de Pizza (1/3) -->
    <div class="glass-panel rounded-2xl p-6 flex flex-col h-full">
        <div class="mb-6">
            <h5 class="font-display text-lg font-bold text-white flex items-center gap-2">
                <i class="ph ph-chart-pie-slice text-brand-accent"></i> Status Geral
            </h5>
        </div>
        <div class="flex-1 relative min-h-[250px] flex items-center justify-center">
            <canvas id="chartPizza"></canvas>
        </div>
        <div class="text-center mt-4 text-xs text-slate-500">
            Total acumulado por status
        </div>
    </div>
</div>

<!-- DETALHES -->
<div class="glass-panel rounded-2xl overflow-hidden mb-8">
    <div class="p-4 border-b border-white/5 bg-white/5">
        <h5 id="tituloTabela" class="font-display text-sm font-bold text-slate-300 flex items-center gap-2">
            <i class="ph ph-list text-brand-accent"></i> Detalhes da Seleção
        </h5>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-black/20 text-xs text-slate-500 uppercase">
                <tr>
                    <th class="px-6 py-3 font-semibold">Proposta / Cliente</th>
                    <th class="px-6 py-3 font-semibold">Data Criação</th>
                    <th class="px-6 py-3 font-semibold text-right">Valor Final</th>
                    <th class="px-6 py-3 font-semibold text-right">Ações</th>
                </tr>
            </thead>
            <tbody id="corpoTabela" class="divide-y divide-white/5">
                <tr>
                    <td colspan="4" class="text-center py-8 text-slate-500">
                        <i class="ph ph-hand-tap text-3xl mb-2 block opacity-50"></i>
                        Clique em uma bolinha no <b>Gráfico de Evolução</b> para ver as propostas aqui.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- SCRIPTS DASHBOARD -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // --- FUNÇÕES CRM ---
    function crmRegistrarTouch(idProposta) {
        // Feedback visual imediato (Optimistic UI)
        const row = document.getElementById(`radar-row-${idProposta}`);
        if(row) {
            row.style.opacity = '0.5';
            row.style.pointerEvents = 'none';
        }

        fetch('ajax_crm_action.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `acao=registrar_touch&id_proposta=${idProposta}`
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                // Remove a linha suavemente
                if(row) row.remove();
                // Verifica se a tabela ficou vazia
                const radarSection = document.getElementById('crm-radar-section');
                const rowsRemaining = radarSection.querySelectorAll('tbody tr').length;
                if(rowsRemaining === 0) radarSection.remove();
            } else {
                alert('Erro ao atualizar: ' + (data.message || 'Erro desconhecido'));
                if(row) row.style.opacity = '1';
            }
        })
        .catch(err => {
            console.error(err);
            if(row) row.style.opacity = '1';
        });
    }

    // --- CORES OFICIAIS SGT ---
    const CORES = {
        'Em elaboração': '#fbbf24', 
        'Enviada':       '#3b82f6', 
        'Aprovada':      '#22c55e', 
        'Cancelada':     '#ef4444'  
    };

    Chart.defaults.color = '#94a3b8'; 
    Chart.defaults.borderColor = '#1e293b'; 

    // 1. GRÁFICO DE LINHA
    const ctxLinha = document.getElementById('chartLinha').getContext('2d');
    if (window.chartLinhaObj instanceof Chart) window.chartLinhaObj.destroy();

    window.chartLinhaObj = new Chart(ctxLinha, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($meses); ?>,
            datasets: [
                { label: 'Aprovada', data: <?php echo json_encode($aprov); ?>, borderColor: CORES['Aprovada'], backgroundColor: CORES['Aprovada'], tension: 0.3, borderWidth: 3 },
                { label: 'Enviada', data: <?php echo json_encode($env); ?>, borderColor: CORES['Enviada'], backgroundColor: CORES['Enviada'], tension: 0.3, borderWidth: 3 },
                { label: 'Em elaboração', data: <?php echo json_encode($elab); ?>, borderColor: CORES['Em elaboração'], backgroundColor: CORES['Em elaboração'], tension: 0.3, borderWidth: 3 },
                { label: 'Cancelada', data: <?php echo json_encode($canc); ?>, borderColor: CORES['Cancelada'], backgroundColor: CORES['Cancelada'], borderDash: [5,5], tension: 0.3, borderWidth: 2 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: { 
                y: { 
                    beginAtZero: true,
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { callback: function(value) { return 'R$ ' + value.toLocaleString('pt-BR'); } }
                },
                x: { grid: { display: false } }
            },
            onClick: (e) => {
                const points = window.chartLinhaObj.getElementsAtEventForMode(e, 'nearest', { intersect: true }, true);
                if (points.length) {
                    const mes = window.chartLinhaObj.data.labels[points[0].index];
                    const status = window.chartLinhaObj.data.datasets[points[0].datasetIndex].label;
                    carregarDetalhes(mes, status);
                }
            },
            plugins: {
                legend: { labels: { usePointStyle: true, padding: 20 } },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#cbd5e1',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) { label += ': '; }
                            label += parseFloat(context.raw).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                            return label;
                        }
                    }
                }
            }
        }
    });

    // 2. GRÁFICO DE PIZZA
    const labelsPizza = <?php echo json_encode($pizza_labels); ?>;
    const valoresPizza = <?php echo json_encode($pizza_valores); ?>;
    const coresPizza = labelsPizza.map(status => CORES[status] || '#6c757d');

    const ctxPizza = document.getElementById('chartPizza').getContext('2d');
    if (window.chartPizzaObj instanceof Chart) window.chartPizzaObj.destroy();

    window.chartPizzaObj = new Chart(ctxPizza, {
        type: 'doughnut',
        data: {
            labels: labelsPizza,
            datasets: [{
                data: valoresPizza,
                backgroundColor: coresPizza,
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { position: 'right', labels: { usePointStyle: true, padding: 20 } },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#cbd5e1',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.raw;
                            let total = 0;
                            try { total = context.chart._metasets[context.datasetIndex].total; } catch(e) { total = 1; }
                            let percentage = Math.round((value / total) * 100) + '%';
                            return ` ${label}: ${percentage} (${value})`;
                        }
                    }
                }
            }
        }
    });

    // 3. AJAX CARREGAR DETALHES
    function carregarDetalhes(mes, status) {
        document.getElementById('tituloTabela').innerText = `Propostas: ${status} em ${mes}`;
        document.getElementById('corpoTabela').innerHTML = '<tr><td colspan="4" class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-brand-accent"></div><div class="mt-2 text-slate-400 text-xs">Carregando dados...</div></td></tr>';
        
        // Formatar Mês MM/YYYY para YYYY-MM para filtro SQL se necessário, ou mandar string mesmo
        fetch(`api_dashboard.php?acao=detalhes&mes=${encodeURIComponent(mes)}&status=${status}`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('corpoTabela').innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                document.getElementById('corpoTabela').innerHTML = '<tr><td colspan="4" class="text-center text-red-400 py-4">Erro ao comunicar com o servidor.</td></tr>';
            });
    }
</script>