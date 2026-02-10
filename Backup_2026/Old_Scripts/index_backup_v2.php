<?php
/**
 * index.php
 * Landing Page Premium - SGT
 * Focada em conversão e autoridade.
 */

session_start();

// Se já está logado, redireciona para o painel
if (isset($_SESSION['usuario_id'])) {
    header('Location: painel.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT - Propostas de Topografia em Minutos</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/landing_premium.css">
</head>
<body>

    <!-- Header -->
    <header>
        <div class="container header-content">
            <a href="#" class="logo">
                <i class="bi bi-grid-fill" style="color: var(--primary);"></i> SGT
            </a>
            <nav>
                <a href="login.php" class="nav-btn" style="color: var(--text-light); margin-right: 1rem;">Login</a>
                <a href="https://api.whatsapp.com/send?phone=5531999999999" target="_blank" class="nav-btn btn-outline">
                    <i class="bi bi-whatsapp"></i> Fale Conosco
                </a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="badge-hero" style="display: inline-block; background: #eff6ff; color: var(--primary); padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; margin-bottom: 1.5rem;">
                🚀 A ferramenta nº 1 para Topógrafos
            </div>
            <h1>Pare de Perder Horas<br>Formatando Propostas no Word.</h1>
            <p>Gere orçamentos técnicos de topografia em 2 minutos. Calcule custos, margem e envie PDFs profissionais com rastreamento.</p>

            <div class="hero-cta-group">
                <a href="criar_conta_demo.php" class="btn-primary">
                    Testar Grátis por 5 Dias
                </a>
                <a href="#como-funciona" class="btn-secondary">
                    <i class="bi bi-play-circle"></i> Ver Como Funciona
                </a>
            </div>

            <div style="margin-top: 3rem; opacity: 0.8;">
                <p style="font-size: 0.9rem; margin-bottom: 0.5rem;">Empresas que confiam:</p>
                <div style="display: flex; gap: 2rem; justify-content: center; filter: grayscale(100%); opacity: 0.6;">
                    <span><i class="bi bi-building"></i> TopoEng</span>
                    <span><i class="bi bi-geo-alt"></i> GeoMaps</span>
                    <span><i class="bi bi-layers"></i> Agrimensura Pro</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Cards de Acesso Rápido (Mantendo funcionalidade antiga mas com design novo) -->
    <section class="container" style="margin-top: -3rem; position: relative; z-index: 10;">
        <div class="access-cards">
            <!-- 1. SOU CLIENTE -->
            <div class="card-access">
                <div class="icon-box"><i class="bi bi-person-fill-lock"></i></div>
                <h3>Já sou Cliente</h3>
                <p>Acesse seu painel de produção e gerencie suas propostas.</p>
                <a href="login_prod.php" class="btn-access btn-primary">Entrar no Sistema</a>
            </div>

            <!-- 2. DEMO -->
            <div class="card-access">
                <div class="icon-box"><i class="bi bi-stars"></i></div>
                <h3>Novo por aqui?</h3>
                <p>Crie uma conta Demo instantânea e teste todas as funcionalidades.</p>
                <a href="criar_conta_demo.php" class="btn-access btn-secondary">Criar Conta Demo</a>
            </div>

            <!-- 3. CONTRATAR -->
            <div class="card-access">
                <div class="icon-box"><i class="bi bi-rocket-takeoff-fill"></i></div>
                <h3>Quero Contratar</h3>
                <p>Profissionalize sua gestão agora. Planos a partir de R$ 97/mês.</p>
                <a href="contratar.php" class="btn-access btn-success">Ver Planos</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="como-funciona">
        <div class="container">
            <div class="section-title">
                <h2>Por que o SGT é diferente?</h2>
                <p>Esqueça planilhas complexas e documentos desformatados.</p>
            </div>
            <div class="grid-features">
                <div class="feature-item">
                    <i class="bi bi-lightning-charge-fill" style="font-size: 2.5rem; color: var(--primary); margin-bottom: 15px; display: block;"></i>
                    <h4>Agilidade Extrema</h4>
                    <p>Preencha apenas os dados variáveis (dias, área). O SGT monta o texto e cálculos para você.</p>
                </div>
                <div class="feature-item">
                    <i class="bi bi-calculator-fill" style="font-size: 2.5rem; color: var(--primary); margin-bottom: 15px; display: block;"></i>
                    <h4>Precificação Exata</h4>
                    <p>Nunca mais pague para trabalhar. O sistema calcula impostos, custos ocultos e sua margem real.</p>
                </div>
                <div class="feature-item">
                    <i class="bi bi-file-earmark-pdf-fill" style="font-size: 2.5rem; color: var(--primary); margin-bottom: 15px; display: block;"></i>
                    <h4>PDFs Impecáveis</h4>
                    <p>Seu cliente recebe uma proposta visualmente incrível, que passa credibilidade e confiança.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-links">
                <a href="#">Termos de Uso</a>
                <a href="#">Política de Privacidade</a>
                <a href="https://api.whatsapp.com/send?phone=5531999999999">Suporte via WhatsApp</a>
                <a href="login.php">Área Administrativa</a>
            </div>
            <p class="copyright">© <?php echo date('Y'); ?> SGT - Sistema de Gestão de Topografia. Todos os direitos reservados.</p>
        </div>
    </footer>

</body>
</html>
