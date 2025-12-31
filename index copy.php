<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT 2025 | Sistema de Gestão Topográfica</title>
    
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Fontes Google (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; overflow-x: hidden; background-color: #f8f9fa; }
        
        /* === BACKGROUND PREMIUM (Engenharia) === */
        .hero-wrapper {
            position: relative;
            background: url('https://images.unsplash.com/photo-1487958449943-2429e8be8625?q=80&w=2070&auto=format&fit=crop') no-repeat center center/cover;
            min-height: 100vh; /* Ocupa a tela toda */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Camada escura para o texto brilhar */
        .hero-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(14, 33, 48, 0.85) 0%, rgba(20, 70, 50, 0.8) 100%);
        }

        /* === O EFEITO DE VIDRO (GLASSMORPHISM) === */
        .glass-card {
            background: rgba(255, 255, 255, 0.1); /* Branco transparente */
            backdrop-filter: blur(16px);           /* O DESFOQUE MÁGICO */
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.2); /* Borda fina de vidro */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); /* Sombra suave */
            border-radius: 24px;
            padding: 3rem;
            color: white;
            max-width: 900px;
            position: relative;
            z-index: 10;
            text-align: center;
            animation: floatUp 1s ease-out;
        }

        @keyframes floatUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Tipografia do Vidro */
        .glass-title { font-weight: 800; font-size: 3.5rem; letter-spacing: -1px; text-shadow: 0 4px 10px rgba(0,0,0,0.3); margin-bottom: 15px; }
        .glass-subtitle { font-size: 1.3rem; font-weight: 300; color: rgba(255,255,255,0.9); margin-bottom: 40px; line-height: 1.6; }
        .badge-glass { background: rgba(37, 211, 102, 0.2); border: 1px solid #25d366; color: #25d366; padding: 5px 15px; border-radius: 50px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; font-weight: bold; display: inline-block; margin-bottom: 20px; }

        /* Botões Especiais */
        .btn-action {
            background-color: #25D366; color: white; border: none;
            padding: 18px 45px; font-size: 1.1rem; font-weight: 800;
            border-radius: 50px; transition: all 0.3s;
            box-shadow: 0 0 20px rgba(37, 211, 102, 0.4);
            text-decoration: none; display: inline-block;
        }
        .btn-action:hover {
            transform: scale(1.05); box-shadow: 0 0 30px rgba(37, 211, 102, 0.6); color: white;
        }
        
        .btn-login {
            background: transparent; border: 1px solid rgba(255,255,255,0.5); color: white;
            padding: 18px 45px; font-size: 1.1rem; font-weight: 600;
            border-radius: 50px; transition: all 0.3s; text-decoration: none; display: inline-block;
        }
        .btn-login:hover { background: rgba(255,255,255,0.1); border-color: white; color: white; }

        /* Navbar Transparente */
        .navbar-glass {
            position: absolute; top: 0; left: 0; width: 100%; z-index: 100;
            padding: 25px 0;
        }
        .navbar-brand { color: white !important; font-weight: 800; font-size: 1.5rem; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }

        /* Seções Abaixo */
        .section-white { background: white; padding: 100px 0; }
        .feature-box { text-align: center; padding: 30px; transition: 0.3s; border-radius: 15px; }
        .feature-box:hover { background: #f8f9fa; transform: translateY(-10px); }
        .icon-circle { width: 80px; height: 80px; background: #e8f5e9; color: #198754; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 20px; }

        @media (max-width: 768px) {
            .glass-title { font-size: 2.5rem; }
            .glass-card { padding: 2rem 1.5rem; margin: 0 15px; }
            .btn-action, .btn-login { width: 100%; margin-bottom: 10px; display: block; }
        }
    </style>
</head>
<body>

    <!-- HERO SECTION COM EFEITO VIDRO -->
    <div class="hero-wrapper">
        <div class="hero-overlay"></div>
        
        <!-- Navbar Flutuante -->
        <nav class="navbar navbar-expand-lg navbar-glass">
            <div class="container">
                <a class="navbar-brand" href="#"><i class="bi bi-grid-fill me-2"></i>SGT</a>
                <!-- Links de Login no Topo -->
                <div class="d-none d-md-block">
                    <a href="login.php" class="text-white text-decoration-none fw-bold me-4">Área do Cliente</a>
                    <a href="criar_conta_demo.php" class="text-white text-decoration-none opacity-75">Criar Conta</a>
                </div>
            </div>
        </nav>

        <!-- O QUADRO DE VIDRO (CTA) -->
        <div class="glass-card">
            <div class="badge-glass">SISTEMA 100% ONLINE</div>
            
            <h1 class="glass-title">Orçamentos de Topografia<br>em Nível Profissional</h1>
            
            <p class="glass-subtitle">
                Abandone o Excel. Automatize cálculos, gere propostas em PDF e envie pelo WhatsApp em segundos. 
                <br>O sistema que trabalha para você.
            </p>

            <div class="d-flex flex-column flex-md-row justify-content-center gap-3">
                <a href="criar_conta_demo.php" class="btn-action">
                    <i class="bi bi-rocket-takeoff-fill me-2"></i> TESTAR GRÁTIS
                </a>
                <a href="login.php" class="btn-login">
                    <i class="bi bi-person-fill me-2"></i> JÁ SOU CLIENTE
                </a>
            </div>

            <div class="mt-4 text-white-50 small">
                <i class="bi bi-check2-circle me-1"></i> Sem cartão de crédito &nbsp;&nbsp;
                <i class="bi bi-check2-circle me-1"></i> Acesso imediato &nbsp;&nbsp;
                <i class="bi bi-check2-circle me-1"></i> Cancelamento fácil
            </div>
        </div>
    </div>

    <!-- SEÇÃO DE RECURSOS (LIMPA E BRANCA) -->
    <section class="section-white">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold display-6 text-dark">Por que escolher o SGT?</h2>
                <p class="text-muted">Engenharia robusta por trás de uma interface simples.</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="icon-circle"><i class="bi bi-file-earmark-pdf-fill"></i></div>
                        <h4 class="fw-bold">Propostas PDF</h4>
                        <p class="text-muted">Geração automática de documentos formatados ABNT com sua logo e dados.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="icon-circle"><i class="bi bi-calculator-fill"></i></div>
                        <h4 class="fw-bold">Cálculo Automático</h4>
                        <p class="text-muted">Cruza custos de equipe, estadia e equipamentos para garantir seu lucro real.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="icon-circle"><i class="bi bi-whatsapp"></i></div>
                        <h4 class="fw-bold">Envio Rápido</h4>
                        <p class="text-muted">Botão integrado para enviar a proposta direto no WhatsApp do cliente.</p>
                    </div>
                </div>
            </div>

            <!-- PRINT DO SISTEMA (PROVA SOCIAL) -->
            <div class="row mt-5 justify-content-center">
                <div class="col-lg-10">
                    <div class="p-2 bg-white border rounded shadow-lg">
                        <!-- Lembre-se de ter essa imagem na pasta -->
                        <img src="assets/img/dashboard_sgt.png" alt="Sistema SGT" class="img-fluid rounded">
                    </div>
                    <div class="text-center mt-3 text-muted small">Dashboard intuitivo e fácil de usar.</div>
                </div>
            </div>

        </div>
    </section>

    <!-- RODAPÉ -->
    <footer style="background: #111; color: #777; padding: 40px 0;">
        <div class="container text-center">
            <h5 class="text-white fw-bold mb-3">SGT Tecnologia</h5>
            <p class="mb-4">Transformando a gestão de engenheiros e topógrafos em todo o Brasil.</p>
            <a href="criar_conta_demo.php" class="text-decoration-none text-success fw-bold me-3">Criar Conta</a>
            <a href="login.php" class="text-decoration-none text-white me-3">Login</a>
            <a href="#" class="text-decoration-none text-muted">Termos de Uso</a>
            <div class="mt-4 border-top border-secondary pt-3 small">
                &copy; 2025 SGT. Todos os direitos reservados.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>