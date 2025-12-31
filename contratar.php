<?php
// Nome do Arquivo: contratar.php
// Função: Tela de Planos com DESTAQUE PARA PIX no plano mensal.

session_start();
require_once 'config.php';

// =============================================================
// 1. CONFIGURAÇÃO FINANCEIRA
// =============================================================

// Preço Base Mensal (R$ 30,00)
$preco_base = 30.00; 

// --- SEUS LINKS REAIS ---

// 1. Link de ASSINATURA (Cartão/Recorrência)
$link_mensal_assinatura = "https://www.mercadopago.com.br/subscriptions/checkout?preapproval_plan_id=6b8610a74e9e4f66aed94c9bd7a957af";

// 2. Link de PAGAMENTO ÚNICO R$ 30,00 (PIX Avulso) - GERE E COLE AQUI
$link_mensal_pix        = "https://mpago.la/2JrbxWt";

// 3. Links dos Planos Longos (Pagamento Único)
$link_trimestral = "https://mpago.la/2BV5xy6";
$link_semestral  = "https://mpago.la/2MjigKn";
$link_anual      = "https://mpago.la/1CuvPFA";

// SEU WHATSAPP COMERCIAL
$whatsapp_comercial = "5531999999999"; 
$link_zap = "https://api.whatsapp.com/send?phone=$whatsapp_comercial&text=Tenho%20duvidas%20sobre%20os%20planos";

// =============================================================
// 2. MOTOR DE CÁLCULO
// =============================================================

function reais($valor) {
    return number_format((float)$valor, 2, ',', '.');
}

$mensal_base = $preco_base;
$mensal_tri  = ($preco_base * 0.95);
$mensal_sem  = ($preco_base * 0.90);
$mensal_anu  = ($preco_base * 0.80);

$total_tri = $mensal_tri * 3;
$total_sem = $mensal_sem * 6;
$total_anu = $mensal_anu * 12;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos de Acesso | <?php echo defined('SITE_NAME') ? SITE_NAME : 'SGT'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; color: #333; }
        .header-compact { text-align: center; padding: 40px 10px 30px; }
        .header-compact h2 { font-weight: 800; color: #2c3e50; font-size: 2rem; }
        
        .card-plan { border: 1px solid #e0e0e0; border-radius: 10px; background: white; transition: all 0.2s; height: 100%; position: relative; overflow: hidden; display: flex; flex-direction: column; }
        .card-plan:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08); border-color: #bdc3c7; }
        
        .plan-title { font-size: 1.1rem; font-weight: bold; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; }
        .price-wrapper { margin: 15px 0; }
        .price-currency { font-size: 1rem; vertical-align: top; top: 5px; position: relative; }
        .price-value { font-size: 2.5rem; font-weight: 800; color: #2c3e50; }
        .price-period { font-size: 0.9rem; color: #95a5a6; }
        .total-billed { font-size: 0.85rem; color: #7f8c8d; background: #f8f9fa; display: inline-block; padding: 4px 10px; border-radius: 20px; margin-bottom: 15px; }

        .plan-anual { border: 2px solid #198754; background-color: #fafffc; }
        .badge-save { position: absolute; top: 15px; right: -30px; background: #198754; color: white; width: 120px; text-align: center; transform: rotate(45deg); font-size: 0.7rem; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }

        .btn-plan { width: 100%; font-weight: bold; padding: 10px; border-radius: 6px; }
        
        /* Área de Botões do Mensal */
        .monthly-actions { margin-top: auto; }
        .separator { display: flex; align-items: center; text-align: center; color: #aaa; font-size: 0.8rem; margin: 8px 0; }
        .separator::before, .separator::after { content: ''; flex: 1; border-bottom: 1px solid #eee; }
        .separator:not(:empty)::before { margin-right: .25em; }
        .separator:not(:empty)::after { margin-left: .25em; }

    </style>
</head>
<body>

    <div class="container pb-5">
        
        <div class="header-compact">
            <h2>Escolha seu Plano</h2>
            <p class="text-muted">Desbloqueie o acesso completo ao sistema.</p>
            <a href="index.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 justify-content-center">
            
            <!-- 1. MENSAL (HÍBRIDO COM DESTAQUE PIX) -->
            <div class="col">
                <div class="card-plan p-3 text-center">
                    <div class="plan-title">Mensal</div>
                    <div class="price-wrapper">
                        <span class="price-currency">R$</span>
                        <span class="price-value"><?php echo reais($mensal_base); ?></span>
                    </div>
                    <div class="total-billed">Sem fidelidade</div>
                    
                    <div class="monthly-actions">
                        <!-- Botão PIX (Verde Chama Atenção) -->
                        <a href="<?php echo $link_mensal_pix; ?>" class="btn btn-plan btn-success text-white mb-2">
                            <i class="bi bi-qr-code-scan"></i> Pagar com PIX
                        </a>

                        <div class="separator">OU</div>

                        <!-- Botão Assinatura (Azul Suave) -->
                        <a href="<?php echo $link_mensal_assinatura; ?>" class="btn btn-plan btn-outline-primary btn-sm">
                            <i class="bi bi-credit-card"></i> Assinatura (Cartão)
                        </a>
                    </div>
                </div>
            </div>

            <!-- 2. TRIMESTRAL -->
            <div class="col">
                <div class="card-plan p-3 text-center">
                    <div class="plan-title text-primary">Trimestral</div>
                    <div class="price-wrapper">
                        <span class="price-currency">R$</span>
                        <span class="price-value"><?php echo reais($mensal_tri); ?></span>
                        <span class="price-period">/mês</span>
                    </div>
                    <div class="total-billed">Total: R$ <?php echo reais($total_tri); ?></div>
                    <div class="small text-success fw-bold mb-2">5% de Desconto</div>
                    
                    <div class="mt-auto">
                        <a href="<?php echo $link_trimestral; ?>" class="btn btn-plan btn-primary">Assinar</a>
                    </div>
                </div>
            </div>

            <!-- 3. SEMESTRAL -->
            <div class="col">
                <div class="card-plan p-3 text-center">
                    <div class="plan-title text-primary">Semestral</div>
                    <div class="price-wrapper">
                        <span class="price-currency">R$</span>
                        <span class="price-value"><?php echo reais($mensal_sem); ?></span>
                        <span class="price-period">/mês</span>
                    </div>
                    <div class="total-billed">Total: R$ <?php echo reais($total_sem); ?></div>
                    <div class="small text-success fw-bold mb-2">10% de Desconto</div>
                    
                    <div class="mt-auto">
                        <a href="<?php echo $link_semestral; ?>" class="btn btn-plan btn-primary">Assinar</a>
                    </div>
                </div>
            </div>

            <!-- 4. ANUAL -->
            <div class="col">
                <div class="card-plan plan-anual p-3 text-center">
                    <div class="badge-save">ECONOMIA</div>
                    <div class="plan-title text-success">Anual</div>
                    <div class="price-wrapper">
                        <span class="price-currency text-success">R$</span>
                        <span class="price-value text-success"><?php echo reais($mensal_anu); ?></span>
                        <span class="price-period">/mês</span>
                    </div>
                    <div class="total-billed text-dark fw-bold">Total: R$ <?php echo reais($total_anu); ?></div>
                    <div class="small text-success fw-bold mb-2">20% OFF (Melhor Valor)</div>
                    
                    <div class="mt-auto">
                        <a href="<?php echo $link_anual; ?>" class="btn btn-plan btn-success text-white shadow-sm">Assinar Agora</a>
                    </div>
                </div>
            </div>

        </div>

        <div class="text-center mt-5">
            <p class="small text-muted mb-2">Pagamento seguro via Mercado Pago. Ativação em até 24h úteis.</p>
            <a href="<?php echo $link_zap; ?>" target="_blank" class="text-decoration-none fw-bold text-success small">
                <i class="bi bi-whatsapp"></i> Dúvidas? Fale Conosco
            </a>
        </div>

    </div>

</body>
</html>