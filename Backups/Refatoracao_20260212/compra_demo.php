<?php
/**
 * compra_demo.php
 * Página de Venda do Acesso Profissional (R$ 10,00)
 * Layout Premium Dark
 */

session_start();
require_once 'config.php';

// Link de Pagamento (Fornecido pelo usuário)
$link_pagamento = "https://mpago.la/1XfmE7K";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Profissional | SGT</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/landing_dark.css">
    <style>
        /* Ajustes específicos para esta página */
        .price-tag {
            font-size: 4rem;
            font-weight: 800;
            color: #00ff88;
            text-shadow: 0 0 20px rgba(0, 255, 136, 0.3);
            margin: 20px 0;
        }
        
        .price-label {
            font-size: 1.2rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .features-list {
            list-style: none;
            padding: 0;
            margin: 30px 0;
            text-align: left;
            display: inline-block;
        }

        .features-list li {
            margin-bottom: 15px;
            font-size: 1.1rem;
            color: #ccc;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .features-list li::before {
            content: '✓';
            color: var(--primary);
            font-weight: bold;
        }

        .btn-buy {
            background: linear-gradient(90deg, #00ff88, #00f2fe);
            color: #000;
            padding: 18px 40px;
            font-size: 1.2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            display: inline-block;
            box-shadow: 0 0 30px rgba(0, 255, 136, 0.2);
            border: none;
            cursor: pointer;
        }

        .btn-buy:hover {
            transform: scale(1.05);
            box-shadow: 0 0 50px rgba(0, 255, 136, 0.4);
        }

        .guarantee {
            margin-top: 20px;
            font-size: 0.9rem;
            color: #666;
        }
    </style>
</head>
<body>

    <header>
        <div class="logo" style="font-weight: bold; font-size: 1.2rem;"><strong>SGT</strong>-Propostas</div>
        <div class="nav-access">
            <a href="index.php" class="btn btn-cliente">Voltar</a>
        </div>
    </header>

    <section class="hero">
        <div class="badge">Ambiente de Produção</div>
        <h1>Acesse o sistema oficial <br><span>Profissional.</span></h1>
        
        <div class="price-label">Acesso Imediato por apenas</div>
        <div class="price-tag">R$ 10,00</div>

        <ul class="features-list">
            <li>Acesso ao Ambiente de Produção (Oficial)</li>
            <li>Geração de propostas reais</li>
            <li>Modelos profissionais inclusos</li>
            <li>Suporte prioritário via WhatsApp</li>
        </ul>

        <a href="<?php echo $link_pagamento; ?>" class="btn-buy">
            Comprar Acesso Pro
        </a>

        <p class="guarantee">
            <i class="bi bi-shield-lock"></i> Pagamento seguro via Mercado Pago
        </p>

        <!-- Card Flutuante Decorativo -->
        <div class="proposal-card" style="margin-top: 60px; animation-delay: 1s;">
            <div class="status-dot"></div>
            <div style="text-align: left;">
                <div style="font-size: 12px; color: #888;">ACESSO LIBERADO</div>
                <div style="font-weight: bold;">Sua conta será ativada instantaneamente</div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p class="copyright">© <?php echo date('Y'); ?> SGT - Sistema de Gestão de Topografia.</p>
        </div>
    </footer>

</body>
</html>
