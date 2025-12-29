<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comece Agora | SGT</title>
    
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Fonte Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; overflow-x: hidden; background-color: #111; color: white; }
        
        /* Fundo Engenharia Escuro */
        .bg-wrapper {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1487958449943-2429e8be8625?q=80&w=2070&auto=format&fit=crop') no-repeat center center/cover;
            z-index: -1;
        }
        .bg-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(0,0,0,0.9) 0%, rgba(20, 70, 50, 0.85) 100%);
        }

        /* Container Central */
        .content-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Efeito de Vidro */
        .glass-panel {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 3rem;
            max-width: 1000px;
            width: 100%;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }

        /* Tipografia */
        h1 { font-weight: 900; font-size: 3rem; margin-bottom: 1rem; letter-spacing: -1px; }
        .text-green { color: #25D366; }
        
        /* Lista de Motivos */
        .feature-list { list-style: none; padding: 0; margin: 30px 0; }
        .feature-list li { 
            font-size: 1.2rem; margin-bottom: 15px; display: flex; align-items: center; color: rgba(255,255,255,0.9);
        }
        .feature-list i { color: #25D366; margin-right: 15px; font-size: 1.4rem; }

        /* Cartões de Ação */
        .action-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .action-card:hover { transform: translateY(-5px); background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.3); }

        .btn-cta {
            width: 100%; padding: 15px; border-radius: 50px; font-weight: 800; text-decoration: none; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s;
        }
        .btn-green { background-color: #25D366; color: #fff; border: none; box-shadow: 0 0 20px rgba(37, 211, 102, 0.3); }
        .btn-green:hover { background-color: #128C7E; color: white; box-shadow: 0 0 30px rgba(37, 211, 102, 0.5); }
        
        .btn-outline { background: transparent; border: 2px solid rgba(255,255,255,0.3); color: white; }
        .btn-outline:hover { background: white; color: black; border-color: white; }

        @media (max-width: 768px) {
            h1 { font-size: 2.2rem; text-align: center; }
            .glass-panel { padding: 1.5rem; }
            .feature-list li { font-size: 1rem; }
        }
    </style>
</head>
<body>

    <div class="bg-wrapper"><div class="bg-overlay"></div></div>

    <div class="content-container">
        <div class="glass-panel">
            <div class="row align-items-center g-5">
                
                <!-- LADO ESQUERDO: Argumentos (Motivos) -->
                <div class="col-lg-7">
                    <span class="badge border border-success text-success mb-3 px-3 py-2 rounded-pill">SISTEMA SGT ONLINE</span>
                    <h1>Você viu a velocidade.<br>Agora tenha o <span class="text-green">Controle.</span></h1>
                    <p class="lead opacity-75">O vídeo mostrou apenas 3 minutos. Imagine o que o SGT fará pela sua empresa em um mês inteiro.</p>

                    <ul class="feature-list">
                        <li><i class="bi bi-check-circle-fill"></i> Gere propostas técnicas em 2 minutos.</li>
                        <li><i class="bi bi-check-circle-fill"></i> Elimine erros de cálculo de diárias.</li>
                        <li><i class="bi bi-check-circle-fill"></i> Envie PDF direto no WhatsApp do cliente.</li>
                        <li><i class="bi bi-check-circle-fill"></i> Banco de dados seguro e blindado.</li>
                    </ul>

                    <p class="small opacity-50 mt-4"><i class="bi bi-lock-fill me-1"></i> Ambiente seguro. Não requer cartão para testar.</p>
                </div>

                <!-- LADO DIREITO: Ação (Botões) -->
                <div class="col-lg-5">
                    <div class="d-grid gap-3">
                        
                        <!-- OPÇÃO 1: TESTE -->
                        <div class="action-card">
                            <h4 class="fw-bold mb-1">Ainda não tenho certeza</h4>
                            <p class="small opacity-75 mb-3">Quero mexer no sistema e ver se funciona pra mim.</p>
                            <a href="criar_conta_demo.php" class="btn-cta btn-outline">
                                <i class="bi bi-stars me-2"></i> TESTAR GRÁTIS
                            </a>
                        </div>

                        <!-- OPÇÃO 2: COMPRA -->
                        <div class="action-card" style="border-color: #25D366;">
                            <h4 class="fw-bold text-green mb-1">Já quero resolver</h4>
                            <p class="small opacity-75 mb-3">Quero garantir o preço promocional agora.</p>
                            <a href="contratar.php" class="btn-cta btn-green">
                                <i class="bi bi-rocket-takeoff-fill me-2"></i> VER PLANOS
                            </a>
                            <div class="small mt-2 text-green fw-bold">A partir de R$ 0,80/dia</div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>