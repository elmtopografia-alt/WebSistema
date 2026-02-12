<?php
// Nome do Arquivo: relatorio_proposta.php
// Função: Relatório Financeiro Detalhado de uma Proposta (Raio-X)

session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }
if (!isset($_GET['id'])) { die("ID da proposta não fornecido."); }

$id_proposta = intval($_GET['id']);
$id_usuario = $_SESSION['usuario_id'];
$is_demo = ($_SESSION['ambiente'] === 'demo');
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

if (!$proposta) { die("Proposta não encontrada ou acesso negado."); }

// 2. Busca Custos Detalhados
function getCustos($conn, $tabela, $id_proposta) {
    $sql = "SELECT * FROM $tabela WHERE id_proposta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_proposta);
    $stmt->execute();
    return $stmt->get_result();
}

$salarios = getCustos($conn, 'Proposta_Salarios', $id_proposta);
$estadia = getCustos($conn, 'Proposta_Estadia', $id_proposta);
$consumos = getCustos($conn, 'Proposta_Consumos', $id_proposta);

// Locação precisa de JOIN para pegar o nome
$sql_loc = "SELECT pl.*, tl.nome as nome_equipamento, m.nome_marca 
            FROM Proposta_Locacao pl 
            LEFT JOIN Tipo_Locacao tl ON pl.id_locacao = tl.id_locacao 
            LEFT JOIN Marcas m ON pl.id_marca = m.id_marca 
            WHERE pl.id_proposta = ?";
$stmt_loc = $conn->prepare($sql_loc);
$stmt_loc->bind_param('i', $id_proposta);
$stmt_loc->execute();
$locacao = $stmt_loc->get_result();

$admin = getCustos($conn, 'Proposta_Custos_Administrativos', $id_proposta);

// 3. Totais e Cálculos
$total_salarios = 0; 
$dados_salarios = [];
while($r = $salarios->fetch_assoc()) {
    // Fórmula: (Qtd * Base * Fator / 30) * Dias
    $custo = ($r['quantidade'] * $r['salario_base'] * $r['fator_encargos'] / 30) * $r['dias'];
    $r['custo_calculado'] = $custo;
    $dados_salarios[] = $r;
    $total_salarios += $custo;
}

$total_estadia = 0;
$dados_estadia = [];
while($r = $estadia->fetch_assoc()) {
    // Fórmula: Qtd * Valor * Dias
    $custo = $r['quantidade'] * $r['valor_unitario'] * $r['dias'];
    $r['custo_calculado'] = $custo;
    $dados_estadia[] = $r;
    $total_estadia += $custo;
}

$total_consumos = 0;
$dados_consumos = [];
while($r = $consumos->fetch_assoc()) {
    // Fórmula: (KmTotal * Litro / KmL) * Qtd
    $kml = $r['consumo_kml'] > 0 ? $r['consumo_kml'] : 1;
    $custo = ($r['km_total'] * $r['valor_litro'] / $kml) * $r['quantidade'];
    $r['custo_calculado'] = $custo;
    $dados_consumos[] = $r;
    $total_consumos += $custo;
}

$total_locacao = 0;
$dados_locacao = [];
while($r = $locacao->fetch_assoc()) {
    // Fórmula: (Qtd * ValorMensal / 30) * Dias
    $custo = ($r['quantidade'] * $r['valor_mensal'] / 30) * $r['dias'];
    $r['custo_calculado'] = $custo;
    $dados_locacao[] = $r;
    $total_locacao += $custo;
}

$total_admin = 0;
$dados_admin = [];
while($r = $admin->fetch_assoc()) {
    // Fórmula: Qtd * Valor
    $custo = $r['quantidade'] * $r['valor'];
    $r['custo_calculado'] = $custo;
    $dados_admin[] = $r;
    $total_admin += $custo;
}

$total_custos = $total_salarios + $total_estadia + $total_consumos + $total_locacao + $total_admin;
$receita_bruta = $proposta['valor_final_proposta'];
$lucro_real = $receita_bruta - $total_custos;
$margem_real = ($receita_bruta > 0) ? ($lucro_real / $receita_bruta) * 100 : 0;

// Preparar dados para o gráfico (Remover Zeros)
$chart_labels = [];
$chart_data = [];
$chart_colors = [];

if ($total_salarios > 0) { $chart_labels[] = 'Equipe'; $chart_data[] = $total_salarios; $chart_colors[] = '#0d6efd'; }
if ($total_estadia > 0) { $chart_labels[] = 'Estadia'; $chart_data[] = $total_estadia; $chart_colors[] = '#6610f2'; }
if ($total_consumos > 0) { $chart_labels[] = 'Combustível'; $chart_data[] = $total_consumos; $chart_colors[] = '#fd7e14'; }
if ($total_locacao > 0) { $chart_labels[] = 'Equipamentos'; $chart_data[] = $total_locacao; $chart_colors[] = '#ffc107'; }
if ($total_admin > 0) { $chart_labels[] = 'Admin'; $chart_data[] = $total_admin; $chart_colors[] = '#6c757d'; }

// Se quiser incluir o Lucro no gráfico (opcional, mas o usuário pediu "divisão financeira")
// O usuário pediu "fatiado por despesas", mas reclamou que "não representa a divisão".
// Vamos manter apenas despesas por enquanto, mas garantir que os zeros sumam.


?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório Financeiro | Proposta #<?= $proposta['numero_proposta'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .card-resumo { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .bg-gradient-success { background: linear-gradient(45deg, #198754, #20c997); color: white; }
        .bg-gradient-danger { background: linear-gradient(45deg, #dc3545, #e35d6a); color: white; }
        .bg-gradient-primary { background: linear-gradient(45deg, #0d6efd, #0dcaf0); color: white; }
        .table-sm td, .table-sm th { font-size: 0.9rem; vertical-align: middle; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="painel.php">
                <img src="<?= BASE_URL ?>/assets/img/logo_sgt.png" alt="SGT" style="height: 40px;">
                <span class="mx-2 text-white-50">|</span>
                <span class="fw-normal">Relatório Financeiro</span>
            </a>
            <a href="painel.php" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left me-2"></i>Voltar</a>
        </div>
    </nav>

    <div class="container pb-5">
        
        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Proposta #<?= $proposta['numero_proposta'] ?></h4>
                <div class="text-muted"><?= htmlspecialchars($proposta['nome_cliente']) ?> | <?= htmlspecialchars($proposta['nome_servico']) ?></div>
            </div>
            <div class="text-end">
                <span class="badge bg-secondary fs-6"><?= $proposta['status'] ?></span>
                <div class="small text-muted mt-1"><?= date('d/m/Y', strtotime($proposta['data_criacao'])) ?></div>
            </div>
        </div>

        <!-- Cards de Resumo -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card card-resumo bg-gradient-primary p-3 h-100">
                    <div class="small text-white-50 text-uppercase fw-bold">Receita Final</div>
                    <div class="display-6 fw-bold">R$ <?= number_format($receita_bruta, 2, ',', '.') ?></div>
                    <div class="small mt-2"><i class="bi bi-wallet2"></i> Valor fechado com cliente</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-resumo bg-gradient-danger p-3 h-100">
                    <div class="small text-white-50 text-uppercase fw-bold">Total de Custos</div>
                    <div class="display-6 fw-bold">R$ <?= number_format($total_custos, 2, ',', '.') ?></div>
                    <div class="small mt-2"><i class="bi bi-graph-down-arrow"></i> Despesas operacionais</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-resumo <?= $lucro_real >= 0 ? 'bg-gradient-success' : 'bg-secondary' ?> p-3 h-100">
                    <div class="small text-white-50 text-uppercase fw-bold">Lucro Real</div>
                    <div class="display-6 fw-bold">R$ <?= number_format($lucro_real, 2, ',', '.') ?></div>
                    <div class="small mt-2 fw-bold"><i class="bi bi-pie-chart-fill"></i> Margem: <?= number_format($margem_real, 1, ',', '.') ?>%</div>
                </div>
            </div>
        </div>

        <!-- Detalhamento -->
        <div class="row">
            <!-- Coluna da Esquerda: Custos -->
            <div class="col-lg-8">
                <div class="card card-resumo mb-4">
                    <div class="card-header bg-white fw-bold py-3"><i class="bi bi-list-check me-2 text-primary"></i>Detalhamento de Custos</div>
                    <div class="card-body p-0">
                        <div class="accordion accordion-flush" id="accordionCustos">
                            
                            <!-- Equipe -->
                            <div class="accordion-item">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-salarios">Equipe (R$ <?= number_format($total_salarios, 2, ',', '.') ?>)</button></h2>
                                <div id="flush-salarios" class="accordion-collapse collapse" data-bs-parent="#accordionCustos">
                                    <div class="accordion-body">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead><tr><th>Função</th><th class="text-end">Qtd</th><th class="text-end">Dias</th><th class="text-end">Total</th></tr></thead>
                                            <tbody>
                                                <?php foreach($dados_salarios as $r): ?>
                                                <tr><td><?= $r['funcao'] ?></td><td class="text-end"><?= $r['quantidade'] ?></td><td class="text-end"><?= $r['dias'] ?></td><td class="text-end fw-bold">R$ <?= number_format($r['custo_calculado'], 2, ',', '.') ?></td></tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Estadia -->
                            <div class="accordion-item">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-estadia">Estadia/Alimentação (R$ <?= number_format($total_estadia, 2, ',', '.') ?>)</button></h2>
                                <div id="flush-estadia" class="accordion-collapse collapse" data-bs-parent="#accordionCustos">
                                    <div class="accordion-body">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead><tr><th>Item</th><th class="text-end">Qtd</th><th class="text-end">Dias</th><th class="text-end">Total</th></tr></thead>
                                            <tbody>
                                                <?php foreach($dados_estadia as $r): ?>
                                                <tr><td><?= $r['tipo'] ?></td><td class="text-end"><?= $r['quantidade'] ?></td><td class="text-end"><?= $r['dias'] ?></td><td class="text-end fw-bold">R$ <?= number_format($r['custo_calculado'], 2, ',', '.') ?></td></tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Consumos -->
                            <div class="accordion-item">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-consumo">Combustível (R$ <?= number_format($total_consumos, 2, ',', '.') ?>)</button></h2>
                                <div id="flush-consumo" class="accordion-collapse collapse" data-bs-parent="#accordionCustos">
                                    <div class="accordion-body">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead><tr><th>Item</th><th class="text-end">Km</th><th class="text-end">Total</th></tr></thead>
                                            <tbody>
                                                <?php foreach($dados_consumos as $r): ?>
                                                <tr><td><?= $r['tipo'] ?></td><td class="text-end"><?= $r['distancia_total'] ?></td><td class="text-end fw-bold">R$ <?= number_format($r['custo_calculado'], 2, ',', '.') ?></td></tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Locação -->
                            <div class="accordion-item">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-locacao">Equipamentos (R$ <?= number_format($total_locacao, 2, ',', '.') ?>)</button></h2>
                                <div id="flush-locacao" class="accordion-collapse collapse" data-bs-parent="#accordionCustos">
                                    <div class="accordion-body">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead><tr><th>Equipamento</th><th class="text-end">Qtd</th><th class="text-end">Total</th></tr></thead>
                                            <tbody>
                                                <?php foreach($dados_locacao as $r): ?>
                                                <tr>
                                                    <td>
                                                        <?= htmlspecialchars($r['nome_equipamento']) ?>
                                                        <?php if(!empty($r['nome_marca'])): ?>
                                                            <div class="small text-muted"><?= htmlspecialchars($r['nome_marca']) ?></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end"><?= $r['quantidade'] ?></td>
                                                    <td class="text-end fw-bold">R$ <?= number_format($r['custo_calculado'], 2, ',', '.') ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Admin -->
                            <div class="accordion-item">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-admin">Administrativo (R$ <?= number_format($total_admin, 2, ',', '.') ?>)</button></h2>
                                <div id="flush-admin" class="accordion-collapse collapse" data-bs-parent="#accordionCustos">
                                    <div class="accordion-body">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead><tr><th>Descrição</th><th class="text-end">Valor</th></tr></thead>
                                            <tbody>
                                                <?php foreach($dados_admin as $r): ?>
                                                <tr><td><?= $r['tipo'] ?></td><td class="text-end fw-bold">R$ <?= number_format($r['custo_calculado'], 2, ',', '.') ?></td></tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Coluna da Direita: Gráfico -->
            <div class="col-lg-4">
                <div class="card card-resumo mb-4">
                    <div class="card-header bg-white fw-bold py-3">Distribuição de Custos</div>
                    <div class="card-body">
                        <canvas id="graficoCustos"></canvas>
                    </div>
                </div>
                <div class="d-grid">
                    <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer me-2"></i>Imprimir Relatório</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Calculator -->
    <div id="calc-container" style="display:none; position:fixed; bottom:80px; right:20px; width:260px; background:#fff; border-radius:10px; box-shadow:0 5px 20px rgba(0,0,0,0.2); z-index:9999; overflow:hidden;">
        <div class="bg-dark text-white p-2 d-flex justify-content-between align-items-center" style="cursor:move;" id="calc-header">
            <small class="fw-bold"><i class="bi bi-calculator me-1"></i> Calculadora</small>
            <button type="button" class="btn-close btn-close-white btn-sm" onclick="toggleCalc()"></button>
        </div>
        <div class="p-3">
            <input type="text" id="calc-display" class="form-control mb-3 text-end fs-5" readonly value="0">
            <div class="row g-2">
                <div class="col-3"><button class="btn btn-light w-100" onclick="calcInput('7')">7</button></div>
                <div class="col-3"><button class="btn btn-light w-100" onclick="calcInput('8')">8</button></div>
                <div class="col-3"><button class="btn btn-light w-100" onclick="calcInput('9')">9</button></div>
                <div class="col-3"><button class="btn btn-secondary w-100" onclick="calcOp('/')">÷</button></div>
                
                <div class="col-3"><button class="btn btn-light w-100" onclick="calcInput('4')">4</button></div>
                <div class="col-3"><button class="btn btn-light w-100" onclick="calcInput('5')">5</button></div>
                <div class="col-3"><button class="btn btn-light w-100" onclick="calcInput('6')">6</button></div>
                <div class="col-3"><button class="btn btn-secondary w-100" onclick="calcOp('*')">×</button></div>
                
                <div class="col-3"><button class="btn btn-light w-100" onclick="calcInput('1')">1</button></div>
                <div class="col-3"><button class="btn btn-light w-100" onclick="calcInput('2')">2</button></div>
                <div class="col-3"><button class="btn btn-light w-100" onclick="calcInput('3')">3</button></div>
                <div class="col-3"><button class="btn btn-secondary w-100" onclick="calcOp('-')">-</button></div>
                
                <div class="col-3"><button class="btn btn-danger w-100" onclick="calcClear()">C</button></div>
                <div class="col-3"><button class="btn btn-light w-100" onclick="calcInput('0')">0</button></div>
                <div class="col-3"><button class="btn btn-primary w-100" onclick="calcResult()">=</button></div>
                <div class="col-3"><button class="btn btn-secondary w-100" onclick="calcOp('+')">+</button></div>
            </div>
        </div>
    </div>

    <button onclick="toggleCalc()" class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="position:fixed; bottom:20px; right:20px; width:50px; height:50px; z-index:9998;" title="Abrir Calculadora">
        <i class="bi bi-calculator fs-4"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Calculator Logic
        let currentInput = '0';
        let operator = null;
        let previousInput = null;
        let newNumber = true;

        function toggleCalc() {
            const el = document.getElementById('calc-container');
            el.style.display = (el.style.display === 'none') ? 'block' : 'none';
        }

        function updateDisplay() {
            document.getElementById('calc-display').value = currentInput;
        }

        function calcInput(num) {
            if (newNumber) {
                currentInput = num;
                newNumber = false;
            } else {
                currentInput = currentInput === '0' ? num : currentInput + num;
            }
            updateDisplay();
        }

        function calcClear() {
            currentInput = '0';
            operator = null;
            previousInput = null;
            newNumber = true;
            updateDisplay();
        }

        function calcOp(op) {
            if (operator !== null) calcResult();
            previousInput = currentInput;
            operator = op;
            newNumber = true;
        }

        function calcResult() {
            if (operator === null || previousInput === null) return;
            const prev = parseFloat(previousInput);
            const curr = parseFloat(currentInput);
            let res = 0;

            switch(operator) {
                case '+': res = prev + curr; break;
                case '-': res = prev - curr; break;
                case '*': res = prev * curr; break;
                case '/': res = prev / curr; break;
            }

            currentInput = String(res);
            operator = null;
            previousInput = null;
            newNumber = true;
            updateDisplay();
        }

        // Draggable Logic
        const calcHeader = document.getElementById('calc-header');
        const calcContainer = document.getElementById('calc-container');
        let isDragging = false;
        let startX, startY, initialLeft, initialTop;

        calcHeader.addEventListener('mousedown', (e) => {
            isDragging = true;
            startX = e.clientX;
            startY = e.clientY;
            const rect = calcContainer.getBoundingClientRect();
            initialLeft = rect.left;
            initialTop = rect.top;
            calcContainer.style.bottom = 'auto';
            calcContainer.style.right = 'auto';
            calcContainer.style.left = initialLeft + 'px';
            calcContainer.style.top = initialTop + 'px';
        });

        document.addEventListener('mousemove', (e) => {
            if (isDragging) {
                const dx = e.clientX - startX;
                const dy = e.clientY - startY;
                calcContainer.style.left = (initialLeft + dx) + 'px';
                calcContainer.style.top = (initialTop + dy) + 'px';
            }
        });

        document.addEventListener('mouseup', () => {
            isDragging = false;
        });

        const ctx = document.getElementById('graficoCustos');

        const chartData = {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [{
                data: <?= json_encode($chart_data) ?>,
                backgroundColor: <?= json_encode($chart_colors) ?>,
                borderWidth: 0
            }]
        };

        new Chart(ctx, {
            type: 'doughnut',
            data: chartData,
            options: {
                responsive: true,
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: {
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map(function(label, i) {
                                        const meta = chart.getDatasetMeta(0);
                                        const ds = data.datasets[0];
                                        const arc = meta.data[i];
                                        const custom = arc && arc.custom || {};
                                        const value = ds.data[i];
                                        const total = ds.data.reduce((acc, val) => acc + val, 0);
                                        const perc = ((value / total) * 100).toFixed(1) + '%';
                                        const valorF = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
                                        
                                        return {
                                            text: `${label}: ${valorF} (${perc})`,
                                            fillStyle: ds.backgroundColor[i],
                                            strokeStyle: ds.backgroundColor[i],
                                            lineWidth: 0,
                                            hidden: isNaN(ds.data[i]) || meta.data[i].hidden,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed !== null) {
                                    label += new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(context.parsed);
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const perc = ((context.parsed / total) * 100).toFixed(1) + '%';
                                    label += ' (' + perc + ')';
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
