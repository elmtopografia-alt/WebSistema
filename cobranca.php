<?php
/**
 * cobranca.php
 * Painel do Cliente - Tela repaginada de cobrança e planos.
 * Design moderno com Tailwind CSS.
 */

session_start();
require_once 'config.php';
require_once 'core/financeiro/FinanceiroService.php';

// Segurança: Apenas Clientes
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login_prod.php');
    exit;
}

$service = new FinanceiroService();
$dados = $service->obterResumoFinanceiro($_SESSION['usuario_id']);
$assinatura = $dados['assinatura'];
$ciclo = $dados['ciclo_atual'];

// Links de Pagamento (Idênticos ao contratar.php)
$link_mensal_pix = "https://mpago.la/2JrbxWt";
$link_mensal_assinatura = "https://www.mercadopago.com.br/subscriptions/checkout?preapproval_plan_id=6b8610a74e9e4f66aed94c9bd7a957af";
$link_trimestral = "https://mpago.la/2BV5xy6";
$link_semestral  = "https://mpago.la/2MjigKn";
$link_anual      = "https://mpago.la/1CuvPFA";
$whatsapp_comercial = "5531999999999";
$link_zap = "https://api.whatsapp.com/send?phone=$whatsapp_comercial&text=Tenho%20duvida%20sobre%20cobranca";

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Assinatura | SGT</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9', // Sky 500
                            600: '#0284c7',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #f8fafc; }
        .gradient-text {
            background: linear-gradient(to right, #ef4444, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
    </style>
</head>
<body class="text-slate-800 antialiased">

<div class="min-h-screen py-10 px-4 sm:px-6 lg:px-8">
    
    <div class="max-w-7xl mx-auto space-y-12">

        <!-- HEADER / VOLTAR -->
        <div class="flex justify-between items-center">
            <a href="painel.php" class="flex items-center text-slate-500 hover:text-brand-600 font-medium transition-colors">
                <i class="bi bi-arrow-left mr-2"></i> Voltar ao Painel
            </a>
            <div class="text-sm text-slate-400">Financeiro Seguro <i class="bi bi-lock-fill"></i></div>
        </div>

        <!-- 1. STATUS ATUAL DA ASSINATURA -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 sm:p-8 flex flex-col md:flex-row justify-between items-center gap-6">
                
                <!-- Info do Plano -->
                <div class="flex-1 text-center md:text-left">
                    <h2 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-1">Seu Plano Atual</h2>
                    <div class="text-3xl font-extrabold text-slate-900">
                        <?php echo $assinatura ? htmlspecialchars($assinatura['plano']) : 'Nenhum Plano Ativo'; ?>
                    </div>
                    <?php if ($assinatura): ?>
                        <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span> Ativo &bull; Renova em <?php echo date('d/m', strtotime('+30 days')); ?>
                        </div>
                    <?php else: ?>
                        <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            <span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span> Inativo
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Status da Fatura -->
                <div class="flex-1 w-full md:w-auto border-t md:border-t-0 md:border-l border-slate-100 pt-6 md:pt-0 md:pl-8">
                    <?php if ($ciclo): ?>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-slate-500 font-medium">Fatura de <?php echo $ciclo['competencia']; ?></span>
                            <?php if ($ciclo['status'] === 'pendente'): ?>
                                <span class="px-2 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded uppercase">Aberta</span>
                            <?php else: ?>
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded uppercase">Paga</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($ciclo['status'] === 'pendente'): ?>
                            <div class="bg-amber-50 border border-amber-100 rounded-lg p-3 mb-3 flex items-start">
                                <i class="bi bi-exclamation-circle text-amber-500 mt-0.5 mr-2"></i>
                                <div class="text-xs text-amber-800">evite bloqueio, regularize hoje.</div>
                            </div>
                            <a href="registrar_pagamento.php?id_ciclo=<?php echo $ciclo['id_ciclo']; ?>" class="block w-full text-center bg-brand-600 hover:bg-brand-500 text-white font-bold py-3 px-4 rounded-xl transition-colors shadow-lg shadow-brand-200">
                                Pagar Agora
                            </a>
                        <?php else: ?>
                             <div class="flex items-center justify-center h-12 bg-slate-50 rounded-xl text-slate-400 font-medium border border-dashed border-slate-200">
                                <i class="bi bi-check-circle-fill text-green-500 mr-2"></i> Tudo em dia!
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                         <div class="text-center text-slate-400 py-2">
                            <i class="bi bi-calendar-check text-2xl block mb-1"></i>
                            <span class="text-sm">Nenhuma fatura gerada.</span>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- 2. OFERTAS IMPERDÍVEIS -->
        <div>
            <div class="text-center mb-10">
                <h1 class="text-3xl md:text-4xl font-extrabold text-slate-800 mb-2">
                    Ei, veja as <span class="gradient-text">ofertas imperdíveis</span><br> que separamos pra você!
                </h1>
                <p class="text-slate-500 mt-4 max-w-2xl mx-auto">
                    Aproveite os preços especiais, garanta sua presença digital e fortaleça sua gestão com o SGT Pro.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

                <!-- CARD: MENSAL -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden card-hover flex flex-col">
                    <div class="h-2 bg-slate-200 w-full"></div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="mb-4">
                            <h3 class="font-bold text-lg text-slate-700">Mensal</h3>
                            <p class="text-xs text-slate-400">Flexibilidade total</p>
                        </div>
                        <div class="mb-6">
                            <span class="text-xs text-slate-400 block mb-1">a partir de</span>
                            <div class="flex items-baseline">
                                <span class="text-sm font-semibold text-slate-500 mr-1">R$</span>
                                <span class="text-4xl font-extrabold text-slate-900">30,00</span>
                                <span class="text-slate-400 text-sm">/mês</span>
                            </div>
                             <div class="mt-2 text-xs font-medium text-slate-400 bg-slate-50 inline-block px-2 py-1 rounded">
                                Cobrado mensalmente
                            </div>
                        </div>

                        <!-- Botões de Ação Mensal -->
                        <div class="mt-auto space-y-3">
                            <a href="<?php echo $link_mensal_pix; ?>" class="block w-full text-center bg-green-500 hover:bg-green-600 text-white font-bold py-2.5 rounded-lg transition-colors text-sm">
                                <i class="bi bi-qr-code"></i> PIX (Libera na hora)
                            </a>
                            <div class="relative flex py-1 items-center">
                                <div class="flex-grow border-t border-slate-100"></div>
                                <span class="flex-shrink-0 mx-2 text-slate-300 text-[10px] uppercase">ou</span>
                                <div class="flex-grow border-t border-slate-100"></div>
                            </div>
                            <a href="<?php echo $link_mensal_assinatura; ?>" class="block w-full text-center border border-brand-200 text-brand-600 hover:bg-brand-50 font-semibold py-2.5 rounded-lg transition-colors text-sm">
                                Assinar Cartão
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CARD: TRIMESTRAL -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden card-hover flex flex-col relative">
                     <div class="absolute top-0 right-0 bg-blue-100 text-blue-600 text-xs font-bold px-3 py-1 rounded-bl-xl">
                        -5% OFF
                    </div>
                    <div class="h-2 bg-blue-400 w-full"></div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="mb-4">
                            <h3 class="font-bold text-lg text-slate-700">Trimestral</h3>
                            <p class="text-xs text-slate-400">Economia inicial</p>
                        </div>
                        <div class="mb-6">
                            <span class="text-xs text-slate-400 block mb-1">equivale a</span>
                            <div class="flex items-baseline">
                                <span class="text-sm font-semibold text-slate-500 mr-1">R$</span>
                                <span class="text-4xl font-extrabold text-slate-900">28,50</span>
                                <span class="text-slate-400 text-sm">/mês</span>
                            </div>
                            <!-- TOTAL TRIMESTRAL -->
                            <div class="mt-3 p-2 bg-blue-50 rounded-lg text-center">
                                <p class="text-[10px] uppercase font-bold text-blue-400 tracking-wider mb-0.5">Total à vista</p>
                                <p class="text-sm font-bold text-blue-700">R$ 85,50</p>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <a href="<?php echo $link_trimestral; ?>" class="block w-full text-center bg-brand-500 hover:bg-brand-600 text-white font-bold py-3 rounded-xl transition-colors shadow-lg shadow-brand-100">
                                Assinar Agora
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CARD: SEMESTRAL -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden card-hover flex flex-col relative">
                    <div class="absolute top-0 right-0 bg-indigo-100 text-indigo-600 text-xs font-bold px-3 py-1 rounded-bl-xl">
                        -10% OFF
                    </div>
                    <div class="h-2 bg-indigo-400 w-full"></div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="mb-4">
                            <h3 class="font-bold text-lg text-slate-700">Semestral</h3>
                            <p class="text-xs text-slate-400">Compromisso médio</p>
                        </div>
                        <div class="mb-6">
                            <span class="text-xs text-slate-400 block mb-1">equivale a</span>
                            <div class="flex items-baseline">
                                <span class="text-sm font-semibold text-slate-500 mr-1">R$</span>
                                <span class="text-4xl font-extrabold text-slate-900">27,00</span>
                                <span class="text-slate-400 text-sm">/mês</span>
                            </div>
                            <!-- TOTAL SEMESTRAL -->
                             <div class="mt-3 p-2 bg-indigo-50 rounded-lg text-center">
                                <p class="text-[10px] uppercase font-bold text-indigo-400 tracking-wider mb-0.5">Total à vista</p>
                                <p class="text-sm font-bold text-indigo-700">R$ 162,00</p>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <a href="<?php echo $link_semestral; ?>" class="block w-full text-center bg-brand-500 hover:bg-brand-600 text-white font-bold py-3 rounded-xl transition-colors shadow-lg shadow-brand-100">
                                Assinar Agora
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CARD: ANUAL (DESTAQUE) -->
                <div class="bg-white rounded-2xl shadow-xl border-2 border-brand-500 overflow-hidden transform md:-translate-y-2 flex flex-col relative">
                     <div class="absolute top-0 inset-x-0 bg-brand-500 text-white text-[10px] font-bold uppercase tracking-widest text-center py-1">
                        Melhor Escolha
                    </div>
                     <div class="absolute top-6 right-0 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold px-3 py-1 rounded-l-full shadow-md">
                        20% OFF
                    </div>
                    <div class="p-6 pt-10 flex-1 flex flex-col">
                        <div class="mb-4">
                            <h3 class="font-bold text-xl text-brand-600">Anual</h3>
                            <p class="text-xs text-slate-400">Máxima economia</p>
                        </div>
                        <div class="mb-6">
                            <span class="text-xs text-slate-400 block mb-1">apenas</span>
                            <div class="flex items-baseline">
                                <span class="text-sm font-semibold text-slate-500 mr-1">R$</span>
                                <span class="text-5xl font-extrabold text-slate-900 tracking-tight">24,00</span>
                                <span class="text-slate-400 text-sm">/mês</span>
                            </div>
                             <!-- TOTAL ANUAL -->
                             <div class="mt-4 p-2 bg-green-50 rounded-lg text-center border border-green-100">
                                <p class="text-[10px] uppercase font-bold text-green-600 tracking-wider mb-0.5">Pagamento único de</p>
                                <p class="text-lg font-extrabold text-green-700">R$ 288,00</p>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <a href="<?php echo $link_anual; ?>" class="block w-full text-center bg-brand-600 hover:bg-brand-700 text-white font-bold py-4 rounded-xl transition-all hover:shadow-xl hover:scale-105 shadow-brand-200">
                                Quero esse Desconto
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer / Dúvidas -->
            <div class="text-center mt-12 mb-8">
                 <a href="<?php echo $link_zap; ?>" target="_blank" class="inline-flex items-center text-slate-400 hover:text-green-600 transition-colors text-sm font-medium">
                    <i class="bi bi-whatsapp mr-2 text-lg"></i> Ficou com alguma dúvida? Fale com a gente
                 </a>
            </div>
            
        </div>

    </div>
</div>

</body>
</html>
