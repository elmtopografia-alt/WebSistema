<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SGT | Sistema de Gestão Topográfica</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Fontes Premium -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #00f2fe;
            --secondary-color: #4facfe;
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-color: #ffffff;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #050505;
            color: var(--text-color);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* FUNDO COM PARALLAX E OVERLAY */
        .hero-wrapper {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url('https://images.unsplash.com/photo-1503387762-592deb58ef4e?q=80&w=2531&auto=format&fit=crop') no-repeat center center/cover;
            background-attachment: fixed;
        }

        .hero-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(5, 10, 20, 0.9) 0%, rgba(10, 25, 45, 0.85) 100%);
            backdrop-filter: blur(5px);
        }

        /* CARD DE VIDRO PREMIUM */
        .glass-card {
            position: relative;
            z-index: 10;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 4rem;
            max-width: 1000px;
            width: 90%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: floatUp 1s ease-out;
        }

        /* TIPOGRAFIA */
        h1.hero-title {
            font-size: 4rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            background: linear-gradient(to right, #fff, #b3cdd1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -1px;
        }

        p.hero-subtitle {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 300;
            margin-bottom: 3rem;
            line-height: 1.6;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        /* BOTÕES MODERNOS */
        .btn-glow {
            background: linear-gradient(90deg, #00f2fe 0%, #4facfe 100%);
            border: none;
            color: #000;
            font-weight: 700;
            padding: 16px 40px;
            border-radius: 50px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 0 20px rgba(79, 172, 254, 0.4);
            text-decoration: none;
            display: inline-block;
        }

        .btn-glow:hover {
            transform: translateY(-3px);
            box-shadow: 0 0 30px rgba(79, 172, 254, 0.6);
            color: #000;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            font-weight: 600;
            padding: 16px 40px;
            border-radius: 50px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
            color: white;
        }

        /* Responsividade Extrema (iPhone/Mobile) */
        @media (max-width: 768px) {
            .hero-wrapper { background-attachment: scroll; } /* Fix iOS parallax bug */
            .glass-card { padding: 2.5rem 1.5rem; width: 95%; }
            h1.hero-title { font-size: 2.5rem; }
            p.hero-subtitle { font-size: 1rem; }
            .d-flex { flex-direction: column; gap: 15px; }
            .btn-glow, .btn-outline { width: 100%; display: block; text-align: center; }
        }

        /* Animação */
        @keyframes floatUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .badge-tech {
            background: rgba(0, 242, 254, 0.1);
            color: #00f2fe;
            border: 1px solid rgba(0, 242, 254, 0.3);
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 20px;
            display: inline-block;
        }
    </style>
</head>
<body>

    <div class="hero-wrapper">
        <div class="hero-overlay"></div>

        <div class="glass-card">
            <div class="badge-tech"><i class="bi bi-cpu me-2"></i>SGT 2025 v2.0</div>
            
            <h1 class="hero-title">Gestão de Propostas<br>Nível Engenharia</h1>
            
            <p class="hero-subtitle">
                O sistema definitivo para topógrafos. Automatize orçamentos, gerencie revisões e gere documentos ABNT com precisão milimétrica.
            </p>

            <div class="d-flex justify-content-center gap-3">
                <a href="criar_conta_demo.php" class="btn-glow">
                    <i class="bi bi-rocket-takeoff-fill me-2"></i> TESTAR GRÁTIS
                </a>
                <a href="login.php" class="btn-outline">
                    <i class="bi bi-lock-fill me-2"></i> ÁREA DO CLIENTE
                </a>
            </div>

            <div class="mt-5 pt-4 border-top border-secondary border-opacity-25 d-flex justify-content-center gap-4 text-white-50 small">
                <span><i class="bi bi-check-circle-fill text-success me-1"></i> Docx Automático</span>
                <span><i class="bi bi-check-circle-fill text-success me-1"></i> Cálculo Real-Time</span>
                <span><i class="bi bi-check-circle-fill text-success me-1"></i> Mobile First</span>
            </div>
        </div>
    </div>

</body>
</html>