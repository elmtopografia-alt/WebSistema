<?php
// Nome do Arquivo: cadastrar.php
// Função: Tela de Planos com Design Premium Light (Estático e Clean).

session_start();
require_once 'config.php';

// =============================================================
// 1. CONFIGURAÇÃO FINANCEIRA
// =============================================================

// Preço Base Mensal (R$ 30,00)
$preco_base = 30.00; 

// --- SEUS LINKS REAIS ---

// 1. Link de ASSINATURA (Prioridade: Cartão/Recorrência)
$link_mensal_assinatura = "https://www.mercadopago.com.br/subscriptions/checkout?preapproval_plan_id=6b8610a74e9e4f66aed94c9bd7a957af";

// 2. Link de PAGAMENTO ÚNICO R$ 30,00 (PIX Avulso)
$link_mensal_pix        = "https://mpago.la/1sxgVDi";

// 3. Links dos Planos Longos (Pagamento Único)
$link_trimestral = "https://mpago.la/2BV5xy6";
$link_semestral  = "https://mpago.la/2MjigKn";
$link_anual      = "https://mpago.la/1CuvPFA";

// SEU WHATSAPP COMERCIAL
$whatsapp_comercial = "5531971875928"; 
$link_zap = "https://api.whatsapp.com/send?phone=$whatsapp_comercial&text=Tenho%20duvidas%20sobre%20os%20planos";

// =============================================================
// 2. MOTOR DE CÁLCULO E FORMATAÇÃO
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
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary: #2563eb;       /* Azul Royal */
            --primary-dark: #1e40af;
            --secondary: #64748b;     /* Cinza Texto */
            --bg-page: #cbd5e1;       /* Fundo cinza médio */
            --white: #ffffff;
            --success: #10b981;
            --border: #e2e8f0;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-page);
            color: #1e293b;
            line-height: 1.5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 20px;
        }

        /* Header */
        .header-section {
            text-align: center;
            margin-bottom: 60px;
        }

        .header-section h2 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 15px 0;
            letter-spacing: -1px;
        }

        .header-section p {
            font-size: 1.1rem;
            color: var(--secondary);
            max-width: 600px;
            margin: 0 auto 30px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 8px 16px;
            border: 1px solid var(--border);
            border-radius: 50px;
            transition: all 0.2s;
            background: white;
        }

        .btn-back:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Grid de Planos */
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 30px;
            align-items: start;
        }

        /* Card do Plano */
        .plan-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            overflow: hidden;
        }

        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1);
            border-color: #cbd5e1;
        }

        /* Destaque para o Anual */
        .plan-card.featured {
            border: 2px solid var(--primary);
            background: #f0f9ff;
        }

        .badge-save {
            position: absolute;
            top: 20px;
            right: -35px;
            background: var(--success);
            color: white;
            padding: 5px 40px;
            transform: rotate(45deg);
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .plan-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--secondary);
            text-transform: uppercase;
            margin-bottom: 20px;
            letter-spacing: 1px;
        }

        .plan-card.featured .plan-name {
            color: var(--primary);
        }

        .price-area {
            margin-bottom: 25px;
        }

        .currency {
            font-size: 1.2rem;
            vertical-align: top;
            color: #64748b;
            font-weight: 600;
        }

        .amount {
            font-size: 3rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }

        .period {
            color: #94a3b8;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .billing-info {
            background: #f1f5f9;
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            color: #475569;
            font-weight: 600;
            margin-bottom: 25px;
        }

        .discount-tag {
            color: var(--success);
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 10px;
            display: block;
        }

        /* Botões */
        .btn-plan {
            display: block;
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
        }

        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: #eff6ff;
        }

        /* Link PIX */
        .pix-option {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }

        .pix-link {
            color: var(--secondary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: color 0.2s;
        }

        .pix-link:hover {
            color: var(--primary);
        }

        /* Footer */
        .footer-note {
            text-align: center;
            margin-top: 50px;
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .footer-note a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .header-section h2 { font-size: 2rem; }
            .plans-grid { grid-template-columns: 1fr; max-width: 400px; margin: 0 auto; }
        }
    </style>
</head>
<body>

    <div class="container">
        
        <div class="header-section">
            <h2>Escolha o Plano Ideal</h2>
            <p>Desbloqueie todas as ferramentas profissionais do SGT agora mesmo.</p>
            <a href="index.php" class="btn-back">
                <i class="bi bi-arrow-left"></i> Voltar ao Início
            </a>
        </div>

        <div class="plans-grid">
            
            <!-- 1. MENSAL -->
            <div class="plan-card">
                <div class="plan-name">Mensal</div>
                <div class="price-area">
                    <span class="currency">R$</span>
                    <span class="amount"><?php echo reais($mensal_base); ?></span>
                    <span class="period">/mês</span>
                </div>
                <div class="billing-info">Cobrança Automática</div>
                
                <a href="<?php echo $link_mensal_assinatura; ?>" class="btn-plan btn-outline">
                    Assinar (Cartão)
                </a>

                <div class="pix-option">
                    <a href="<?php echo $link_mensal_pix; ?>" class="pix-link">
                        <i class="bi bi-qr-code"></i> Pagar via PIX (30 dias)
                    </a>
                </div>
            </div>

            <!-- 2. TRIMESTRAL -->
            <div class="plan-card">
                <div class="plan-name">Trimestral</div>
                <div class="price-area">
                    <span class="currency">R$</span>
                    <span class="amount"><?php echo reais($mensal_tri); ?></span>
                    <span class="period">/mês</span>
                </div>
                <div class="billing-info">Total: R$ <?php echo reais($total_tri); ?></div>
                <span class="discount-tag">5% OFF</span>
                
                <a href="<?php echo $link_trimestral; ?>" class="btn-plan btn-primary">
                    Escolher Trimestral
                </a>
            </div>

            <!-- 3. SEMESTRAL -->
            <div class="plan-card">
                <div class="plan-name">Semestral</div>
                <div class="price-area">
                    <span class="currency">R$</span>
                    <span class="amount"><?php echo reais($mensal_sem); ?></span>
                    <span class="period">/mês</span>
                </div>
                <div class="billing-info">Total: R$ <?php echo reais($total_sem); ?></div>
                <span class="discount-tag">10% OFF</span>
                
                <a href="<?php echo $link_semestral; ?>" class="btn-plan btn-primary">
                    Escolher Semestral
                </a>
            </div>

            <!-- 4. ANUAL (DESTAQUE) -->
            <div class="plan-card featured">
                <div class="badge-save">MELHOR OPÇÃO</div>
                <div class="plan-name">Anual</div>
                <div class="price-area">
                    <span class="currency">R$</span>
                    <span class="amount"><?php echo reais($mensal_anu); ?></span>
                    <span class="period">/mês</span>
                </div>
                <div class="billing-info">Total: R$ <?php echo reais($total_anu); ?></div>
                <span class="discount-tag">20% OFF</span>
                
                <a href="<?php echo $link_anual; ?>" class="btn-plan btn-primary">
                    Quero Economizar
                </a>
            </div>

        </div>

        <div class="footer-note">
            Pagamento 100% seguro processado pelo Mercado Pago.<br>
            Dúvidas? <a href="<?php echo $link_zap; ?>" target="_blank">Fale conosco no WhatsApp</a>
        </div>

    </div>

</body>
</html>
