<?php
// ARQUIVO: iniciar_nova_proposta.php
// OBJETIVO: Wizard de Onboarding (UX-001)
// VERSÃO: 2.0 (Glassmorphism + Wizard)

require_once 'config.php';
require_once 'db.php';
require_once 'session_validator.php';

$id_usuario = $_SESSION['usuario_id'];
$ambiente = $_SESSION['ambiente'] ?? 'producao';
$conn = ($ambiente === 'demo') ? Database::getDemo() : Database::getProd();

// 1. Buscar Clientes (Top 50 recentes + Ordem Alfabética)
// Otimizado: Pegamos ID, Nome, Empresa e Foto/Inicial
$sql_clientes = "SELECT id_cliente, nome_cliente, empresa FROM Clientes WHERE id_criador = ? ORDER BY id_cliente DESC LIMIT 50";
$stmt = $conn->prepare($sql_clientes);
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$res_clientes = $stmt->get_result();
$clientes = [];
while ($row = $res_clientes->fetch_assoc()) {
    $clientes[] = $row;
}

// 2. Buscar Serviços
$sql_servicos = "SELECT id_servico, nome, descricao FROM Tipo_Servicos ORDER BY nome ASC";
$res_servicos = $conn->query($sql_servicos);
$servicos = [];
if ($res_servicos) {
    while ($row = $res_servicos->fetch_assoc()) {
        $servicos[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Proposta | Wizard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], display: ['Exo 2', 'sans-serif'] },
                    colors: { brand: { accent: '#F97316', dark: '#0A0F1A', surface: '#1F2937' } }
                }
            }
        }
    </script>
    <style>
        body { background-color: #0A0F1A; color: #F8FAFC; }
        .glass-panel { background: rgba(31, 41, 55, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .step-active { border-color: #F97316; background: rgba(249, 115, 22, 0.1); color: white; }
        .step-inactive { border-color: rgba(255,255,255,0.1); color: #64748B; }
        .card-select:hover { border-color: #F97316; transform: translateY(-2px); box-shadow: 0 10px 30px -10px rgba(249, 115, 22, 0.3); }
        .card-selected { border-color: #F97316; background: rgba(249, 115, 22, 0.1); }
        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0A0F1A; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <!-- Navbar Simplificada -->
    <nav class="fixed top-0 w-full glass-panel z-50 h-16 flex items-center px-6 border-b-0">
        <a href="painel.php" class="flex items-center gap-2 text-slate-400 hover:text-white transition-colors">
            <i class="ph ph-arrow-left text-xl"></i>
            <span class="font-bold text-sm">Voltar ao Painel</span>
        </a>
        <div class="mx-auto font-display text-xl font-bold tracking-wider text-white">NOVA PROPOSTA</div>
        <div class="w-20"></div> <!-- Spacer -->
    </nav>

    <!-- Main Container -->
    <main class="flex-1 pt-24 pb-12 px-4 max-w-5xl mx-auto w-full">
        
        <!-- Steps Indicator -->
        <div class="flex justify-between items-center mb-12 relative">
            <div class="absolute top-1/2 left-0 w-full h-1 bg-white/5 -z-10 rounded-full"></div>
            <!-- Step 1 -->
            <div class="flex flex-col items-center gap-2 bg-[#0A0F1A] px-4 z-10">
                <div id="ind-1" class="w-10 h-10 rounded-full flex items-center justify-center border-2 step-active font-bold transition-all">1</div>
                <span class="text-xs font-bold uppercase tracking-wider text-white">Cliente</span>
            </div>
            <!-- Step 2 -->
            <div class="flex flex-col items-center gap-2 bg-[#0A0F1A] px-4 z-10">
                <div id="ind-2" class="w-10 h-10 rounded-full flex items-center justify-center border-2 step-inactive font-bold transition-all">2</div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Serviço</span>
            </div>
            <!-- Step 3 -->
            <div class="flex flex-col items-center gap-2 bg-[#0A0F1A] px-4 z-10">
                <div id="ind-3" class="w-10 h-10 rounded-full flex items-center justify-center border-2 step-inactive font-bold transition-all">3</div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Confirmar</span>
            </div>
        </div>

        <!-- Form Container -->
        <form id="wizardForm" action="criar_proposta_dinamica.php" method="GET" class="relative min-h-[400px]">
            <input type="hidden" name="nova" value="1">
            <input type="hidden" name="id_cliente" id="input_cliente">
            <input type="hidden" name="id_servico" id="input_servico">

            <!-- STEP 1: CLIENTE -->
            <div id="step-1" class="transition-all duration-300">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-white mb-2">Para quem é esta proposta?</h2>
                    <p class="text-slate-400">Selecione um cliente existente ou crie um novo.</p>
                </div>

                <!-- Busca e Ação -->
                <div class="flex flex-col md:flex-row gap-4 mb-6">
                    <div class="relative flex-1">
                        <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-lg"></i>
                        <input type="text" id="buscaCliente" class="w-full bg-white/5 border border-white/10 rounded-xl py-3 pl-12 text-white placeholder-slate-500 focus:border-brand-accent focus:outline-none transition-all" placeholder="Buscar por nome ou empresa...">
                    </div>
                    <a href="form_cliente.php?retorno=nova_proposta" class="px-6 py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-brand-accent font-bold flex items-center justify-center gap-2 transition-all">
                        <i class="ph ph-plus-circle text-lg"></i> Novo Cliente
                    </a>
                </div>

                <!-- Grid Clientes -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[400px] overflow-y-auto pr-2" id="gridClientes">
                    <?php foreach ($clientes as $c): 
                        $avatar = strtoupper(substr($c['nome_cliente'], 0, 1));
                    ?>
                    <div class="glass-panel p-4 rounded-xl cursor-pointer card-select transition-all border-l-4 border-l-transparent" onclick="selectCliente(this, <?= $c['id_cliente'] ?>, '<?= $c['nome_cliente'] ?>')">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-slate-700 to-slate-800 flex items-center justify-center text-white font-bold text-lg border border-white/5 shadow-inner">
                                <?= $avatar ?>
                            </div>
                            <div>
                                <h3 class="font-bold text-white text-sm"><?= $c['nome_cliente'] ?></h3>
                                <p class="text-xs text-slate-400 truncate max-w-[150px]"><?= $c['empresa'] ?: 'Pessoal' ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-8 text-center">
                    <button type="button" onclick="nextStep(2)" id="btnStep1" class="px-8 py-3 bg-slate-700 text-slate-400 rounded-xl font-bold cursor-not-allowed transition-all" disabled>
                        Continuar <i class="ph ph-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>

            <!-- STEP 2: SERVIÇO -->
            <div id="step-2" class="hidden transition-all duration-300">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-white mb-2">Qual o tipo de serviço?</h2>
                    <p class="text-slate-400">Isso definirá o modelo inicial do documento.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[400px] overflow-y-auto">
                    <?php foreach ($servicos as $s): ?>
                    <div class="glass-panel p-6 rounded-xl cursor-pointer card-select transition-all flex flex-col items-center text-center gap-3 group" onclick="selectServico(this, <?= $s['id_servico'] ?>, '<?= $s['nome'] ?>')">
                        <div class="w-16 h-16 rounded-2xl bg-brand-accent/10 flex items-center justify-center text-brand-accent text-3xl mb-2 group-hover:scale-110 transition-transform">
                            <i class="ph ph-file-text"></i>
                        </div>
                        <h3 class="font-bold text-white"><?= $s['nome'] ?></h3>
                        <p class="text-xs text-slate-500 line-clamp-2"><?= $s['descricao'] ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-8 flex justify-center gap-4">
                    <button type="button" onclick="prevStep(1)" class="px-6 py-3 bg-transparent hover:bg-white/5 text-slate-400 rounded-xl font-bold transition-all">
                        Voltar
                    </button>
                    <button type="button" onclick="nextStep(3)" id="btnStep2" class="px-8 py-3 bg-slate-700 text-slate-400 rounded-xl font-bold cursor-not-allowed transition-all" disabled>
                        Continuar <i class="ph ph-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>

            <!-- STEP 3: CONFIRMAÇÃO -->
            <div id="step-3" class="hidden transition-all duration-300">
                <div class="text-center mb-12">
                    <h2 class="text-2xl font-bold text-white mb-2">Tudo pronto!</h2>
                    <p class="text-slate-400">Vamos criar o rascunho inicial.</p>
                </div>

                <div class="max-w-md mx-auto glass-panel p-8 rounded-2xl border border-brand-accent/30 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-brand-accent/10 rounded-bl-full -mr-10 -mt-10 blur-xl"></div>
                    
                    <div class="flex items-center gap-4 mb-6 pb-6 border-b border-white/5">
                        <div class="w-12 h-12 rounded-full bg-green-500/20 text-green-500 flex items-center justify-center text-xl border border-green-500/30">
                            <i class="ph ph-check"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 uppercase tracking-widest">Cliente Selecionado</p>
                            <h3 class="text-white font-bold text-lg" id="summaryCliente">...</h3>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-blue-500/20 text-blue-500 flex items-center justify-center text-xl border border-blue-500/30">
                            <i class="ph ph-files"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 uppercase tracking-widest">Modelo de Serviço</p>
                            <h3 class="text-white font-bold text-lg" id="summaryServico">...</h3>
                        </div>
                    </div>
                </div>

                <div class="mt-12 flex justify-center gap-4">
                    <button type="button" onclick="prevStep(2)" class="px-6 py-3 bg-transparent hover:bg-white/5 text-slate-400 rounded-xl font-bold transition-all">
                        Voltar
                    </button>
                    <button type="submit" class="px-10 py-4 bg-brand-accent hover:bg-orange-600 text-white rounded-xl font-bold shadow-lg shadow-orange-500/20 hover:shadow-orange-500/40 transition-all transform hover:scale-105 flex items-center gap-2">
                        <i class="ph ph-arrow-right text-xl"></i> PRÓXIMO
                    </button>
                </div>
            </div>

        </form>
    </main>

    <script>
        // State
        let currentStep = 1;

        // Functions
        function nextStep(step) {
            document.getElementById(`step-${currentStep}`).classList.add('hidden');
            document.getElementById(`step-${step}`).classList.remove('hidden');
            
            // Update Indicators
            document.getElementById(`ind-${currentStep}`).classList.replace('step-active', 'step-inactive');
            document.getElementById(`ind-${step}`).classList.replace('step-inactive', 'step-active');
            
            // Highlight done
            document.getElementById(`ind-${currentStep}`).classList.add('bg-green-500', 'border-green-500', 'text-white');
            document.getElementById(`ind-${currentStep}`).innerHTML = '<i class="ph ph-check"></i>';

            currentStep = step;
        }

        function prevStep(step) {
            document.getElementById(`step-${currentStep}`).classList.add('hidden');
            document.getElementById(`step-${step}`).classList.remove('hidden');

            document.getElementById(`ind-${currentStep}`).classList.replace('step-active', 'step-inactive');
            document.getElementById(`ind-${step}`).classList.replace('step-inactive', 'step-active');

            // Reset done state of target
            document.getElementById(`ind-${step}`).classList.remove('bg-green-500', 'border-green-500', 'text-white');
            document.getElementById(`ind-${step}`).innerHTML = step;

            currentStep = step;
        }

        function selectCliente(el, id, nome) {
            // Remove active class from siblings
            document.querySelectorAll('#gridClientes .glass-panel').forEach(c => c.classList.remove('card-selected'));
            el.classList.add('card-selected');
            
            document.getElementById('input_cliente').value = id;
            document.getElementById('summaryCliente').innerText = nome;
            
            // Enable button
            const btn = document.getElementById('btnStep1');
            btn.disabled = false;
            btn.classList.remove('bg-slate-700', 'text-slate-400', 'cursor-not-allowed');
            btn.classList.add('bg-brand-accent', 'text-white', 'hover:bg-orange-600', 'shadow-lg');
            
            // Auto advance (optional, maybe wait for click)
            // setTimeout(() => nextStep(2), 300);
        }

        function selectServico(el, id, nome) {
            document.querySelectorAll('#step-2 .glass-panel').forEach(c => c.classList.remove('card-selected'));
            el.classList.add('card-selected');
            
            document.getElementById('input_servico').value = id;
            document.getElementById('summaryServico').innerText = nome;

            const btn = document.getElementById('btnStep2');
            btn.disabled = false;
            btn.classList.remove('bg-slate-700', 'text-slate-400', 'cursor-not-allowed');
            btn.classList.add('bg-brand-accent', 'text-white', 'hover:bg-orange-600', 'shadow-lg');
        }

        // Search
        document.getElementById('buscaCliente').addEventListener('keyup', function() {
            const val = this.value.toLowerCase();
            document.querySelectorAll('#gridClientes > div').forEach(card => {
                const text = card.innerText.toLowerCase();
                if(text.includes(val)) card.style.display = 'block';
                else card.style.display = 'none';
            });
        });
    </script>
</body>
</html>