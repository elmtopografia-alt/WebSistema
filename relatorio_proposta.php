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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Financeiro | Proposta #<?= $proposta['numero_proposta'] ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Exo 2', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            dark: '#001e3c',
                            primary: '#0a2e5c',
                            surface: '#132f4c',
                            accent: '#FF7518',   // Abóbora Vibrante
                            action: '#EA580C',
                            glow: '#4fc3f7',
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Glassmorphism */
        .glass-panel {
            background: rgba(10, 46, 92, 0.65);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
        }
        .glass-card {
            background: rgba(19, 47, 76, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            transition: all 0.3s ease;
        }
        .glass-card:hover {
            background: rgba(10, 46, 92, 0.85);
            border-color: rgba(255, 117, 24, 0.6);
            transform: translateY(-5px);
            box-shadow: 0 12px 35px -10px rgba(255, 117, 24, 0.3);
        }
        
        /* Background */
        body {
            background: radial-gradient(circle at center, #0a2e5c 0%, #001224 100%);
            min-height: 100vh;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #001224; }
        ::-webkit-scrollbar-thumb { background: #1e40af; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #FF7518; }
    </style>
</head>
<body class="text-slate-200 font-sans antialiased selection:bg-brand-accent selection:text-brand-dark">

    <!-- Navbar Simplificada -->
    <nav class="w-full glass-panel sticky top-0 z-50 border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-4">
                    <img src="<?= BASE_URL ?>/assets/img/logo_sgt.png" alt="SGT" class="h-12">
                    <div class="h-8 w-px bg-white/20 mx-2"></div>
                    <span class="font-display font-bold text-2xl text-white tracking-wide leading-none mt-1">Relatório de Fechamento</span>
                </div>
                <a href="painel.php" class="flex items-center gap-2 text-sm text-slate-300 hover:text-brand-accent transition-colors">
                    <i class="ph ph-arrow-left text-lg"></i> Voltar
                </a>
            </div>
        </div>
    </nav>

    <!-- Compacted Padding: py-6 pb-12 -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-12">
        
        <!-- Header / Hero: Reduced mb-6 -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-3 py-1 rounded-full bg-brand-accent/20 text-brand-accent text-xs font-bold border border-brand-accent/30 uppercase tracking-wider">
                        Proposta #<?= $proposta['numero_proposta'] ?>
                    </span>
                    <span class="px-3 py-1 rounded-full bg-white/10 text-slate-300 text-xs font-bold border border-white/10 uppercase tracking-wider">
                        <?= $proposta['status'] ?>
                    </span>
                </div>
                <h1 class="font-display text-3xl md:text-4xl font-bold text-white">
                    <?= htmlspecialchars($proposta['nome_cliente']) ?>
                </h1>
                <p class="text-blue-200/60 text-lg mt-1">
                    <?= htmlspecialchars($proposta['nome_servico']) ?> • <span class="text-sm"><?= date('d/m/Y', strtotime($proposta['data_criacao'])) ?></span>
                </p>
            </div>
            <div class="flex gap-3">
                <button onclick="window.print()" class="glass-panel px-4 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-white/10 transition-all flex items-center gap-2 text-sm font-medium">
                    <i class="ph ph-printer text-lg"></i> Imprimir
                </button>
            </div>
        </div>

        <!-- Cards de Resumo (KPIs): Reduced mb-6, p-5, icon/text sizes -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Receita -->
            <div class="glass-card p-5 rounded-2xl relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <i class="ph ph-wallet text-5xl text-blue-400"></i>
                </div>
                <p class="text-blue-300 text-sm font-bold uppercase tracking-wider mb-2">Receita Final</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-slate-400 text-lg">R$</span>
                    <span class="font-display text-3xl font-bold text-white group-hover:text-blue-200 transition-colors">
                        <?= number_format($receita_bruta, 2, ',', '.') ?>
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-2">Valor fechado com cliente</p>
            </div>

            <!-- Custos -->
            <div class="glass-card p-5 rounded-2xl relative overflow-hidden group border-red-500/20 hover:border-red-500/50">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <i class="ph ph-trend-down text-5xl text-red-400"></i>
                </div>
                <p class="text-red-300 text-sm font-bold uppercase tracking-wider mb-2">Total de Custos</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-slate-400 text-lg">R$</span>
                    <span class="font-display text-3xl font-bold text-white group-hover:text-red-200 transition-colors">
                        <?= number_format($total_custos, 2, ',', '.') ?>
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-2">Despesas operacionais</p>
            </div>

            <!-- Lucro -->
            <div class="glass-card p-5 rounded-2xl relative overflow-hidden group border-brand-accent/30 bg-brand-accent/5 hover:bg-brand-accent/10">
                <div class="absolute -inset-1 bg-gradient-to-r from-brand-accent/0 via-brand-accent/10 to-brand-accent/0 opacity-0 group-hover:opacity-100 blur transition-opacity duration-500"></div>
                
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <i class="ph ph-coins text-5xl text-brand-accent"></i>
                </div>
                <p class="text-brand-accent text-sm font-bold uppercase tracking-wider mb-2">Lucro Real</p>
                <div class="flex items-baseline gap-1 relative z-10">
                    <span class="text-slate-400 text-lg">R$</span>
                    <span class="font-display text-4xl font-bold text-white drop-shadow-[0_0_10px_rgba(255,117,24,0.5)]">
                        <?= number_format($lucro_real, 2, ',', '.') ?>
                    </span>
                </div>
                <div class="flex items-center gap-2 mt-2 relative z-10">
                    <span class="px-2 py-0.5 rounded bg-brand-accent/20 text-brand-accent text-xs font-bold border border-brand-accent/30">
                        Margem: <?= number_format($margem_real, 1, ',', '.') ?>%
                    </span>
                </div>
            </div>
        </div>

        <!-- Main Grid: Reduced gap-6 -->
        <div class="grid lg:grid-cols-3 gap-6">
            
            <!-- Coluna Esquerda: Detalhamento -->
            <div class="lg:col-span-2 space-y-6">
                <h3 class="font-display text-xl font-bold text-white flex items-center gap-2">
                    <i class="ph ph-list-dashes text-brand-accent"></i> Detalhamento de Custos
                </h3>

                <div class="space-y-4">
                    
                    <!-- Helper Function for Accordion Item -->
                    <?php 
                    function renderAccordionItem($id, $title, $total, $icon, $items, $type = 'generic') {
                        if ($total <= 0) return;
                        $totalF = number_format($total, 2, ',', '.');
                        ?>
                        <div class="glass-panel rounded-xl overflow-hidden">
                            <!-- Compacted Button: px-5 py-3 -->
                            <button onclick="toggleAccordion('<?= $id ?>')" class="w-full px-5 py-3 flex items-center justify-between hover:bg-white/5 transition-colors text-left">
                                <div class="flex items-center gap-3">
                                    <!-- Compacted Icon Container: w-8 h-8 -->
                                    <div class="w-8 h-8 rounded-lg bg-brand-primary/50 flex items-center justify-center text-brand-glow">
                                        <i class="ph <?= $icon ?> text-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white"><?= $title ?></h4>
                                        <p class="text-xs text-slate-400">Clique para ver detalhes</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="block font-display font-bold text-lg text-white">R$ <?= $totalF ?></span>
                                    <i class="ph ph-caret-down text-slate-500 transition-transform" id="icon-<?= $id ?>"></i>
                                </div>
                            </button>
                            
                            <div id="<?= $id ?>" class="hidden border-t border-white/5 bg-black/20">
                                <div class="p-4 overflow-x-auto">
                                    <table class="w-full text-sm text-left">
                                        <thead class="text-xs text-slate-500 uppercase bg-white/5">
                                            <tr>
                                                <th class="px-4 py-2 rounded-l-lg">Descrição</th>
                                                <th class="px-4 py-2 text-right">Qtd</th>
                                                <?php if($type !== 'admin'): ?><th class="px-4 py-2 text-right">Ref/Dias/Km</th><?php endif; ?>
                                                <th class="px-4 py-2 text-right rounded-r-lg">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-white/5">
                                            <?php foreach($items as $item): ?>
                                            <tr class="hover:bg-white/5 transition-colors">
                                                <td class="px-4 py-3 font-medium text-slate-200">
                                                    <?= isset($item['funcao']) ? $item['funcao'] : (isset($item['nome_equipamento']) ? $item['nome_equipamento'] : $item['tipo']) ?>
                                                    <?php if(isset($item['nome_marca'])) echo "<div class='text-xs text-slate-500'>{$item['nome_marca']}</div>"; ?>
                                                </td>
                                                <td class="px-4 py-3 text-right text-slate-400"><?= $item['quantidade'] ?></td>
                                                <?php if($type !== 'admin'): ?>
                                                    <td class="px-4 py-3 text-right text-slate-400">
                                                        <?= isset($item['dias']) ? $item['dias'] : (isset($item['distancia_total']) ? $item['distancia_total'] . ' km' : '-') ?>
                                                    </td>
                                                <?php endif; ?>
                                                <td class="px-4 py-3 text-right font-bold text-brand-glow">
                                                    R$ <?= number_format($item['custo_calculado'], 2, ',', '.') ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <!-- Render Items -->
                    <?php renderAccordionItem('acc-salarios', 'Equipe Técnica', $total_salarios, 'ph-users', $dados_salarios); ?>
                    <?php renderAccordionItem('acc-estadia', 'Estadia & Alimentação', $total_estadia, 'ph-bed', $dados_estadia); ?>
                    <?php renderAccordionItem('acc-consumos', 'Combustível & Logística', $total_consumos, 'ph-gas-pump', $dados_consumos); ?>
                    <?php renderAccordionItem('acc-locacao', 'Locação de Equipamentos', $total_locacao, 'ph-speaker-hifi', $dados_locacao); ?>
                    <?php renderAccordionItem('acc-admin', 'Custos Administrativos', $total_admin, 'ph-files', $dados_admin, 'admin'); ?>

                </div>
            </div>

            <!-- Coluna Direita: Gráfico -->
            <div class="lg:col-span-1">
                <!-- Compacted Card: p-5 -->
                <div class="glass-card p-5 rounded-2xl sticky top-24">
                    <!-- Compacted Title: mb-4 -->
                    <h3 class="font-display text-lg font-bold text-white mb-4 text-center">Distribuição Financeira</h3>
                    
                    <div class="relative aspect-square">
                        <canvas id="graficoCustos"></canvas>
                        <!-- Center Text -->
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="text-center">
                                <span class="text-xs text-slate-400 uppercase">Total</span>
                                <div class="font-bold text-white text-lg">R$ <?= number_format($total_custos, 2, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 space-y-2">
                        <div class="text-xs text-center text-slate-500">
                            * Valores baseados nos custos cadastrados.
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Floating Calculator (Glassmorphism) -->
    <div id="calc-container" class="hidden fixed bottom-24 right-6 w-72 glass-panel rounded-2xl overflow-hidden shadow-2xl z-50 border border-brand-accent/20">
        <div id="calc-header" class="bg-brand-dark/90 p-3 flex justify-between items-center cursor-move border-b border-white/10">
            <span class="text-xs font-bold text-brand-accent uppercase tracking-wider flex items-center gap-2">
                <i class="ph ph-calculator"></i> Calculadora
            </span>
            <button onclick="toggleCalc()" class="text-slate-400 hover:text-white transition-colors">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <div class="p-4 bg-brand-surface/90">
            <input type="text" id="calc-display" class="w-full bg-black/30 border border-white/10 rounded-lg p-3 text-right text-2xl font-display text-white mb-4 focus:outline-none" readonly value="0">
            
            <div class="grid grid-cols-4 gap-2">
                <button class="p-3 rounded-lg bg-white/5 hover:bg-white/10 text-white font-bold transition-colors" onclick="calcInput('7')">7</button>
                <button class="p-3 rounded-lg bg-white/5 hover:bg-white/10 text-white font-bold transition-colors" onclick="calcInput('8')">8</button>
                <button class="p-3 rounded-lg bg-white/5 hover:bg-white/10 text-white font-bold transition-colors" onclick="calcInput('9')">9</button>
                <button class="p-3 rounded-lg bg-brand-primary/50 hover:bg-brand-primary text-brand-glow font-bold transition-colors" onclick="calcOp('/')">÷</button>
                
                <button class="p-3 rounded-lg bg-white/5 hover:bg-white/10 text-white font-bold transition-colors" onclick="calcInput('4')">4</button>
                <button class="p-3 rounded-lg bg-white/5 hover:bg-white/10 text-white font-bold transition-colors" onclick="calcInput('5')">5</button>
                <button class="p-3 rounded-lg bg-white/5 hover:bg-white/10 text-white font-bold transition-colors" onclick="calcInput('6')">6</button>
                <button class="p-3 rounded-lg bg-brand-primary/50 hover:bg-brand-primary text-brand-glow font-bold transition-colors" onclick="calcOp('*')">×</button>
                
                <button class="p-3 rounded-lg bg-white/5 hover:bg-white/10 text-white font-bold transition-colors" onclick="calcInput('1')">1</button>
                <button class="p-3 rounded-lg bg-white/5 hover:bg-white/10 text-white font-bold transition-colors" onclick="calcInput('2')">2</button>
                <button class="p-3 rounded-lg bg-white/5 hover:bg-white/10 text-white font-bold transition-colors" onclick="calcInput('3')">3</button>
                <button class="p-3 rounded-lg bg-brand-primary/50 hover:bg-brand-primary text-brand-glow font-bold transition-colors" onclick="calcOp('-')">-</button>
                
                <button class="p-3 rounded-lg bg-red-500/20 hover:bg-red-500/40 text-red-300 font-bold transition-colors" onclick="calcClear()">C</button>
                <button class="p-3 rounded-lg bg-white/5 hover:bg-white/10 text-white font-bold transition-colors" onclick="calcInput('0')">0</button>
                <button class="p-3 rounded-lg bg-brand-accent hover:bg-brand-action text-white font-bold transition-colors shadow-lg shadow-brand-accent/20" onclick="calcResult()">=</button>
                <button class="p-3 rounded-lg bg-brand-primary/50 hover:bg-brand-primary text-brand-glow font-bold transition-colors" onclick="calcOp('+')">+</button>
            </div>
        </div>
    </div>

    <!-- Floating Button -->
    <button onclick="toggleCalc()" class="fixed bottom-6 right-6 w-14 h-14 bg-brand-accent hover:bg-brand-action text-white rounded-full shadow-[0_0_20px_rgba(255,117,24,0.4)] flex items-center justify-center transition-all hover:scale-110 z-40">
        <i class="ph ph-calculator text-2xl"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Accordion Logic
        function toggleAccordion(id) {
            const el = document.getElementById(id);
            const icon = document.getElementById('icon-' + id);
            if (el.classList.contains('hidden')) {
                el.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                el.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }

        // Calculator Logic
        let currentInput = '0';
        let operator = null;
        let previousInput = null;
        let newNumber = true;

        function toggleCalc() {
            const el = document.getElementById('calc-container');
            el.classList.toggle('hidden');
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
            
            // Remove bottom/right positioning to allow absolute positioning
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

        // Chart Logic
        const ctx = document.getElementById('graficoCustos');
        const chartData = {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [{
                data: <?= json_encode($chart_data) ?>,
                backgroundColor: [
                    '#0ea5e9', // Sky 500 (Equipe)
                    '#8b5cf6', // Violet 500 (Estadia)
                    '#f97316', // Orange 500 (Combustível)
                    '#eab308', // Yellow 500 (Equipamentos)
                    '#64748b'  // Slate 500 (Admin)
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        };

        new Chart(ctx, {
            type: 'doughnut',
            data: chartData,
            options: {
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: {
                            color: '#cbd5e1', // Slate 300
                            font: { family: 'Inter', size: 12 },
                            padding: 20,
                            usePointStyle: true,
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#cbd5e1',
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: true,
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
