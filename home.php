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
        body { font-family: 'Inter', sans-serif; overflow-x: hidden; }
        
        /* Navbar */
        .navbar { padding: 15px 0; transition: all 0.3s; background-color: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .navbar-brand { font-weight: 800; font-size: 1.5rem; color: #2c3e50; }
        .nav-link { font-weight: 600; color: #555; margin: 0 10px; }
        .btn-nav { border-radius: 50px; padding: 8px 25px; font-weight: bold; }

        /* Hero Section (Topo) */
        .hero { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 100px 0 80px; position: relative; overflow: hidden; }
        .hero h1 { font-weight: 800; font-size: 3.5rem; line-height: 1.2; color: #2c3e50; margin-bottom: 20px; }
        .hero p { font-size: 1.25rem; color: #6c757d; margin-bottom: 30px; }
        .hero-img { box-shadow: 0 20px 50px rgba(0,0,0,0.15); border-radius: 15px; border: 1px solid #dee2e6; transform: perspective(1000px) rotateY(-10deg) rotateX(5deg); transition: transform 0.5s; }
        .hero-img:hover { transform: perspective(1000px) rotateY(0deg) rotateX(0deg); }

        /* Features */
        .feature-icon { width: 60px; height: 60px; background-color: #e8f5e9; color: #198754; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin-bottom: 20px; }
        .feature-card { padding: 30px; border: none; transition: all 0.3s; height: 100%; }
        .feature-card:hover { transform: translateY(-5px); }

        /* Call to Action */
        .cta-section { background: #198754; color: white; padding: 80px 0; text-align: center; }
        .btn-cta { background: white; color: #198754; padding: 15px 40px; font-size: 1.2rem; font-weight: 800; border-radius: 50px; transition: transform 0.2s; }
        .btn-cta:hover { transform: scale(1.05); color: #146c43; }

        /* Footer */
        footer { background: #212529; color: #aaa; padding: 50px 0; }
        footer h5 { color: white; margin-bottom: 20px; }
        footer a { color: #aaa; text-decoration: none; }
        footer a:hover { color: white; }

        @media (max-width: 768px) {
            .hero h1 { font-size: 2.5rem; }
            .hero { padding: 60px 0; }
            .hero-img { transform: none; margin-top: 40px; }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="bi bi-grid-fill text-success me-2"></i>SGT <span class="badge bg-light text-success border ms-2" style="font-size: 0.6em;">v1.0</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#recursos">Recursos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#beneficios">Benefícios</a></li>
                    <!-- Link para o Hub de Login -->
                    <li class="nav-item ms-lg-3">
                        <a href="login.php" class="btn btn-outline-secondary btn-nav me-2">Entrar</a>
                    </li>
                    <li class="nav-item">
                        <!-- Link direto para a Demo -->
                        <a href="login.php" class="btn btn-success btn-nav shadow-sm">Testar Grátis</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero d-flex align-items-center">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <span class="text-success fw-bold text-uppercase ls-2">Lançamento 2025</span>
                    <h1>Orçamentos de Topografia em <span class="text-success">Nível Profissional</span></h1>
                    <p>Abandone as planilhas complexas. O SGT é a ferramenta definitiva para gerar propostas comerciais, calcular custos de campo e gerenciar clientes em um só lugar.</p>
                    <div class="d-flex gap-3">
                        <a href="login.php" class="btn btn-success btn-lg fw-bold px-4 shadow">Começar Agora</a>
                        <a href="#recursos" class="btn btn-outline-dark btn-lg fw-bold px-4">Saiba Mais</a>
                    </div>
                    <div class="mt-4 text-muted small">
                        <i class="bi bi-check-circle-fill text-success me-1"></i> PDF Automático
                        <i class="bi bi-check-circle-fill text-success ms-3 me-1"></i> Envio WhatsApp
                        <i class="bi bi-check-circle-fill text-success ms-3 me-1"></i> Backup na Nuvem
                    </div>
                </div>
                <div class="col-lg-6">
                    <!-- Imagem 1: Proposta / Rodapé -->
                    <div class="hero-img bg-white p-2">
                        <img src="img/cria_proposta.png" alt="Sistema SGT" class="img-fluid rounded border">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- RECURSOS -->
    <section id="recursos" class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold display-6">Tudo o que você precisa</h2>
                <p class="text-muted">Desenvolvido por quem entende de campo e escritório.</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-file-earmark-word"></i></div>
                        <h4>Propostas em PDF/DOCX</h4>
                        <p class="text-muted">Gere documentos formatados automaticamente com sua logo, dados do cliente e escopo detalhado. Adeus Ctrl+C Ctrl+V.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-calculator"></i></div>
                        <h4>Cálculo Preciso</h4>
                        <p class="text-muted">O sistema calcula custos de estadia, alimentação, combustível e hora-técnica para garantir sua margem de lucro.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-whatsapp"></i></div>
                        <h4>Envio Inteligente</h4>
                        <p class="text-muted">Envie a proposta direto para o WhatsApp do cliente com um clique, com mensagem personalizada e profissional.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- BENEFÍCIOS (AQUI ENTRA O RELATÓRIO) -->
    <section id="beneficios" class="py-5 bg-light">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-md-6 order-md-2">
                    <!-- Imagem 2: Relatório -->
                    <img src="img/relatorio.png" class="img-fluid rounded shadow-sm mb-4 mb-md-0" alt="Relatórios">
                </div>
                <div class="col-md-6 order-md-1">
                    <h2 class="fw-bold mb-4">Tenha o controle do seu negócio</h2>
                    
                    <div class="d-flex mb-4">
                        <div class="me-3"><i class="bi bi-graph-up-arrow text-primary fs-3"></i></div>
                        <div>
                            <h5>Relatórios Financeiros</h5>
                            <p class="text-muted">Saiba exatamente quanto orçou no mês, qual seu ticket médio e a taxa de conversão.</p>
                        </div>
                    </div>

                    <div class="d-flex mb-4">
                        <div class="me-3"><i class="bi bi-cloud-check text-primary fs-3"></i></div>
                        <div>
                            <h5>Acesso de Qualquer Lugar</h5>
                            <p class="text-muted">100% Online. Acesse do escritório, de casa ou direto da obra pelo celular.</p>
                        </div>
                    </div>

                    <div class="d-flex">
                        <div class="me-3"><i class="bi bi-shield-lock text-primary fs-3"></i></div>
                        <div>
                            <h5>Segurança Total</h5>
                            <p class="text-muted">Seus dados e de seus clientes protegidos em ambiente seguro com backup diário.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA FINAL -->
    <section class="cta-section">
        <div class="container">
            <h2 class="fw-bold mb-4 display-5">Pronto para profissionalizar sua gestão?</h2>
            <p class="fs-5 mb-5 opacity-75">Junte-se a empresas de topografia que já estão economizando tempo.</p>
            <a href="login.php" class="btn-cta text-decoration-none shadow-lg">FAZER TESTE GRÁTIS</a>
            <p class="mt-3 small opacity-75">Sem compromisso. Não requer cartão de crédito para testar.</p>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold text-white">SGT 2025</h5>
                    <p class="small">O sistema de gestão definitivo para engenheiros e topógrafos. Simplicidade e potência no seu dia a dia.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Links Rápidos</h5>
                    <ul class="list-unstyled">
                        <li><a href="login.php">Área do Cliente</a></li>
                        <li><a href="login.php">Criar Conta Demo</a></li>
                        <li><a href="#">Termos de Uso</a></li>
                        <li><a href="#">Política de Privacidade</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Contato</h5>
                    <p class="small">
                        <i class="bi bi-whatsapp me-2"></i> (31) 99999-9999<br>
                        <i class="bi bi-envelope me-2"></i> contato@elmtopografia.com.br
                    </p>
                </div>
            </div>
            <div class="border-top border-secondary pt-4 mt-4 text-center small">
                &copy; 2025 ELM Tecnologia. Todos os direitos reservados.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>