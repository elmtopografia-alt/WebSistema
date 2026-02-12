<?php
// Nome do Arquivo: contratar.php
// Função: Tela de Planos com DESTAQUE PARA PIX no plano mensal. (Design Clean/Dark)

session_start();
require_once 'config.php';

// =============================================================
// 1. CONFIGURAÇÃO FINANCEIRA
// =============================================================

// Preço Base Mensal (R$ 30,00)
$preco_base = 30.00; 

// --- SEUS LINKS REAIS ---
$link_mensal_assinatura = "https://www.mercadopago.com.br/subscriptions/checkout?preapproval_plan_id=6b8610a74e9e4f66aed94c9bd7a957af";
$link_mensal_pix        = "https://mpago.la/2JrbxWt";
$link_trimestral = "https://mpago.la/2BV5xy6";
$link_semestral  = "https://mpago.la/2MjigKn";
$link_anual      = "https://mpago.la/1CuvPFA";

$whatsapp_comercial = "5531999999999"; 
$link_zap = "https://api.whatsapp.com/send?phone=$whatsapp_comercial&text=Tenho%20duvidas%20sobre%20os%20planos";

// =============================================================
// 2. MOTOR DE CÁLCULO
// =============================================================

function reais($valor) {
    return number_format((float)$valor, 2, ',', '.');
}

// Preços Reais
$mensal_base = $preco_base;
$mensal_tri  = ($preco_base * 0.95);
$mensal_sem  = ($preco_base * 0.90);
$mensal_anu  = ($preco_base * 0.80);

// Totais Reais
$total_tri = $mensal_tri * 3;
$total_sem = $mensal_sem * 6;
$total_anu = $mensal_anu * 12;

// Preços "Âncora" (10% mais caros para riscar)
$mensal_base_fake = $mensal_base * 1.1;
$mensal_tri_fake  = $mensal_tri * 1.1;
$mensal_sem_fake  = $mensal_sem * 1.1;
$mensal_anu_fake  = $mensal_anu * 1.1;

?>
<!DOCTYPE html>
<html lang="pt-br" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT | Planos de Acesso</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
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
                        background: '#0a0f1a',
                        surface: '#111827',
                        primary: '#f97316', 
                        secondary: '#3b82f6',
                    },
                    animation: {
                        'pulse-glow': 'pulse-glow 2s ease-in-out infinite',
                    },
                    keyframes: {
                        'pulse-glow': {
                            '0%, 100%': { boxShadow: '0 0 20px rgba(249, 115, 22, 0.4)' },
                            '50%': { boxShadow: '0 0 40px rgba(249, 115, 22, 0.6)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #0a0f1a;
            color: #f8fafc;
        }
        
        /* Glass Effect */
        .glass {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05); /* Match new dashboard style */
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(249, 115, 22, 0.5);
            transform: translateY(-4px);
            box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.3);
        }

        /* Grid Background pattern */
        .grid-bg {
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
        }
        
        .text-gradient {
            background: linear-gradient(135deg, #f97316 0%, #fb923c 50%, #fbbf24 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="antialiased overflow-x-hidden min-h-screen relative flex flex-col items-center">

    <!-- Background Effects -->
    <div class="fixed inset-0 grid-bg opacity-30 pointer-events-none z-0"></div>
    <div class="fixed top-0 left-1/2 w-full h-[500px] bg-blue-600/10 rounded-full blur-[120px] pointer-events-none z-0 transform -translate-x-1/2 -translate-y-1/2"></div>
    
    <!-- Back Button -->
    <div class="absolute top-6 left-6 z-50">
        <a href="index.php" class="flex items-center gap-2 px-4 py-2 rounded-full glass hover:bg-white/10 text-slate-300 hover:text-white transition-colors text-sm font-medium">
            <i class="ph ph-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="relative z-10 w-full max-w-7xl mx-auto px-4 py-16 flex flex-col items-center">
        
        <!-- Header -->
        <div class="text-center mb-16 max-w-3xl">
            <span class="inline-block px-4 py-1.5 rounded-full bg-orange-500/10 text-orange-400 text-xs font-bold border border-orange-500/20 mb-4 uppercase tracking-wider">
                Assinatura Premium
            </span>
            <h1 class="text-4xl md:text-5xl font-display font-bold text-white mb-6">
                Escolha o plano ideal para <span class="text-gradient">maximizar suas vendas</span>
            </h1>
            <p class="text-slate-400 text-lg">
                Desbloqueie acesso ilimitado a todas as ferramentas do SGT Propostas.
            </p>
        </div>

        <!-- Plans Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 w-full">
            
            <!-- 1. MENSAL -->
            <div class="glass-card rounded-2xl p-6 flex flex-col relative overflow-hidden group">
                <div class="mb-4">
                    <h3 class="text-lg font-bold text-slate-300">Mensal</h3>
                    <p class="text-xs text-slate-500">Pagamento Flexível</p>
                </div>
                
                <div class="mb-6">
                    <div class="text-sm text-slate-500 line-through decoration-white mb-1">
                        R$ <?= reais($mensal_base_fake) ?>
                    </div>
                    <div class="flex items-end gap-1">
                        <span class="text-2xl text-slate-400 font-bold mb-1">R$</span>
                        <span class="text-4xl text-white font-bold font-display"><?= reais($mensal_base) ?></span>
                        <span class="text-slate-500 text-sm mb-1.5">/mês</span>
                    </div>
                    <div class="mt-2 text-xs font-bold text-slate-500 bg-white/5 py-1 px-2 rounded inline-block">
                        Sem fidelidade
                    </div>
                </div>

                <ul class="space-y-3 mb-8 text-sm text-slate-300 flex-1">
                    <li class="flex items-center gap-2"><i class="ph ph-check text-orange-400"></i> Propostas Ilimitadas</li>
                    <li class="flex items-center gap-2"><i class="ph ph-check text-orange-400"></i> Dashboard Completo</li>
                    <li class="flex items-center gap-2"><i class="ph ph-check text-orange-400"></i> Envio por PDF/Email</li>
                </ul>

                <div class="space-y-3 mt-auto">
                    <a href="<?= $link_mensal_pix ?>" class="block w-full py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 text-white font-bold text-center rounded-xl shadow-lg shadow-emerald-500/20 transition-all transform hover:-translate-y-0.5">
                        <i class="ph ph-qr-code mr-2"></i> Pagar com PIX
                    </a>
                    <div class="relative flex py-1 items-center">
                        <div class="flex-grow border-t border-white/10"></div>
                        <span class="flex-shrink mx-2 text-xs text-slate-500 uppercase">ou</span>
                        <div class="flex-grow border-t border-white/10"></div>
                    </div>
                    <a href="<?= $link_mensal_assinatura ?>" class="block w-full py-2.5 bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white font-semibold text-center rounded-xl border border-white/10 transition-colors">
                        <i class="ph ph-credit-card mr-2"></i> Cartão
                    </a>
                </div>
            </div>

            <!-- 2. TRIMESTRAL -->
            <div class="glass-card rounded-2xl p-6 flex flex-col relative">
                <div class="mb-4">
                    <h3 class="text-lg font-bold text-blue-400">Trimestral</h3>
                    <p class="text-xs text-slate-500">Renovação a cada 3 meses</p>
                </div>
                
                <div class="mb-6">
                    <div class="text-sm text-slate-500 line-through decoration-white mb-1">
                        R$ <?= reais($mensal_tri_fake) ?>
                    </div>
                    <div class="flex items-end gap-1">
                        <span class="text-2xl text-slate-400 font-bold mb-1">R$</span>
                        <span class="text-4xl text-white font-bold font-display"><?= reais($mensal_tri) ?></span>
                        <span class="text-slate-500 text-sm mb-1.5">/mês</span>
                    </div>
                    <div class="mt-2 text-xs font-bold text-blue-400 bg-blue-500/10 border border-blue-500/20 py-1 px-2 rounded inline-block">
                        Economize 5%
                    </div>
                </div>

                <div class="mb-8 p-3 rounded-lg bg-white/5 border border-white/10 text-center">
                    <p class="text-xs text-slate-400">Pagamento Único</p>
                    <p class="text-xl font-bold text-white">R$ <?= reais($total_tri) ?></p>
                </div>

                <div class="mt-auto">
                    <a href="<?= $link_trimestral ?>" class="block w-full py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold text-center rounded-xl shadow-lg shadow-blue-500/20 transition-all hover:scale-[1.02]">
                        Assinar Trimestral
                    </a>
                </div>
            </div>

            <!-- 3. SEMESTRAL -->
            <div class="glass-card rounded-2xl p-6 flex flex-col relative">
                <div class="mb-4">
                    <h3 class="text-lg font-bold text-purple-400">Semestral</h3>
                    <p class="text-xs text-slate-500">Renovação a cada 6 meses</p>
                </div>
                
                <div class="mb-6">
                    <div class="text-sm text-slate-500 line-through decoration-white mb-1">
                        R$ <?= reais($mensal_sem_fake) ?>
                    </div>
                    <div class="flex items-end gap-1">
                        <span class="text-2xl text-slate-400 font-bold mb-1">R$</span>
                        <span class="text-4xl text-white font-bold font-display"><?= reais($mensal_sem) ?></span>
                        <span class="text-slate-500 text-sm mb-1.5">/mês</span>
                    </div>
                    <div class="mt-2 text-xs font-bold text-purple-400 bg-purple-500/10 border border-purple-500/20 py-1 px-2 rounded inline-block">
                        Economize 10%
                    </div>
                </div>

                <div class="mb-8 p-3 rounded-lg bg-white/5 border border-white/10 text-center">
                    <p class="text-xs text-slate-400">Pagamento Único</p>
                    <p class="text-xl font-bold text-white">R$ <?= reais($total_sem) ?></p>
                </div>

                <div class="mt-auto">
                    <a href="<?= $link_semestral ?>" class="block w-full py-3 bg-purple-600 hover:bg-purple-500 text-white font-bold text-center rounded-xl shadow-lg shadow-purple-500/20 transition-all hover:scale-[1.02]">
                        Assinar Semestral
                    </a>
                </div>
            </div>

            <!-- 4. ANUAL (Destaque) -->
            <div class="glass-card rounded-2xl p-6 flex flex-col relative border border-orange-500/30 bg-orange-500/5 overflow-hidden transform md:-translate-y-4">
                <div class="absolute top-0 right-0 bg-orange-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl shadow-lg">
                    MAIS POPULAR
                </div>
                
                <div class="mb-4 pt-2">
                    <h3 class="text-lg font-bold text-orange-400 flex items-center gap-2">
                        <i class="ph ph-crown-simple text-xl"></i> Anual
                    </h3>
                    <p class="text-xs text-orange-200/60">Melhor custo-benefício</p>
                </div>
                
                <div class="mb-6">
                    <div class="text-sm text-slate-500 line-through decoration-white mb-1">
                        R$ <?= reais($mensal_anu_fake) ?>
                    </div>
                    <div class="flex items-end gap-1">
                        <span class="text-2xl text-orange-500/60 font-bold mb-1">R$</span>
                        <span class="text-5xl text-white font-bold font-display"><?= reais($mensal_anu) ?></span>
                        <span class="text-slate-500 text-sm mb-1.5">/mês</span>
                    </div>
                    <div class="mt-3 text-xs font-bold text-white bg-gradient-to-r from-orange-500 to-orange-600 py-1 px-3 rounded-full inline-block shadow-lg shadow-orange-500/20">
                        🔥 20% OFF
                    </div>
                </div>

                <div class="mb-8 p-4 rounded-xl bg-black/20 border border-orange-500/20 text-center relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-xs text-slate-400 mb-1">Pagamento Único Anual</p>
                        <p class="text-2xl font-bold text-white">R$ <?= reais($total_anu) ?></p>
                    </div>
                </div>

                <div class="mt-auto">
                    <a href="<?= $link_anual ?>" class="block w-full py-4 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-400 hover:to-orange-500 text-white font-bold text-center rounded-xl shadow-lg shadow-orange-500/40 transition-all transform hover:-translate-y-1 hover:shadow-orange-500/60">
                        ASSINAR AGORA
                    </a>
                    <p class="text-[10px] text-center text-slate-500 mt-3">
                        <i class="ph ph-shield-check text-green-500 mr-1"></i> Garantia de 7 dias
                    </p>
                </div>
            </div>

        </div>

        <!-- Footer Info -->
        <div class="mt-16 text-center">
            <p class="text-slate-500 text-sm mb-4">Pagamento 100% seguro via Mercado Pago. Liberação imediata após confirmação.</p>
            <a href="<?= $link_zap ?>" target="_blank" class="inline-flex items-center gap-2 text-green-400 hover:text-green-300 font-bold transition-colors">
                <i class="ph ph-whatsapp-logo text-lg"></i>
                Dúvidas? Fale conosco no WhatsApp
            </a>
        </div>

    </div>

</body>
</html>