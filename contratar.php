<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Planos | SGT</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .pricing .box { padding: 40px 20px; background: #fff; text-align: center; border-radius: 8px; position: relative; overflow: hidden; border: 1px solid #eef0ef; box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: 0.3s; }
        .pricing .box:hover { transform: scale(1.05); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .pricing h3 { font-weight: 700; margin-bottom: 15px; font-size: 20px; color: #012970; }
        .pricing h4 { font-size: 48px; color: #4154f1; font-weight: 700; font-family: "Open Sans", sans-serif; margin-bottom: 25px; }
        .pricing h4 sup { font-size: 28px; }
        .pricing h4 span { color: #bababa; font-size: 18px; font-weight: 400; }
        .pricing ul { padding: 0; list-style: none; color: #444444; text-align: left; line-height: 20px; padding-left: 15px; }
        .pricing ul li { padding-bottom: 16px; }
        .pricing ul i { color: #198754; font-size: 18px; padding-right: 6px; }
        .pricing .btn-buy { display: inline-block; padding: 10px 40px; border-radius: 50px; border: 1px solid #4154f1; color: #4154f1; font-size: 16px; font-weight: 600; transition: 0.3s; text-decoration: none; }
        .pricing .btn-buy:hover { background: #4154f1; color: #fff; }
        
        /* Destaque */
        .pricing .featured { border-color: #4154f1; background: #fff; box-shadow: 0 5px 25px rgba(0,0,0,0.1); transform: scale(1.02); z-index: 10; }
        .pricing .featured .btn-buy { background: #4154f1; color: #fff; }
    </style>
</head>
<body style="background-color: #f6f9ff;">

    <header class="p-3 bg-white shadow-sm mb-5">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php" class="text-decoration-none fs-4 fw-bold text-primary">SGT 2025</a>
            <a href="login.php" class="btn btn-outline-primary rounded-pill btn-sm px-4">Login</a>
        </div>
    </header>

    <main class="container py-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-primary">Nossos Planos</h2>
            <p class="text-muted">Escolha a melhor opção para o seu negócio</p>
        </div>

        <section class="pricing">
            <div class="row g-4">
                
                <!-- Plano Básico -->
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="box">
                        <h3>INICIANTE</h3>
                        <h4><sup>R$</sup>49<span> / mês</span></h4>
                        <ul>
                            <li><i class="bi bi-check"></i> Propostas Ilimitadas</li>
                            <li><i class="bi bi-check"></i> Geração de PDF/Word</li>
                            <li><i class="bi bi-check"></i> 1 Usuário</li>
                            <li class="text-muted text-decoration-line-through"><i class="bi bi-x"></i> Histórico de Revisões</li>
                            <li class="text-muted text-decoration-line-through"><i class="bi bi-x"></i> Backup Automático</li>
                        </ul>
                        <a href="https://wa.me/5531999999999?text=Quero%20plano%20Iniciante" class="btn-buy">Contratar</a>
                    </div>
                </div>

                <!-- Plano Profissional (Destaque) -->
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="box featured">
                        <span class="badge bg-primary position-absolute top-0 end-0 m-3">Recomendado</span>
                        <h3>PROFISSIONAL</h3>
                        <h4><sup>R$</sup>89<span> / mês</span></h4>
                        <ul>
                            <li><i class="bi bi-check"></i> <strong>Tudo do plano Iniciante</strong></li>
                            <li><i class="bi bi-check"></i> Controle de Revisões (Rv)</li>
                            <li><i class="bi bi-check"></i> Envio por WhatsApp</li>
                            <li><i class="bi bi-check"></i> Até 3 Usuários</li>
                            <li><i class="bi bi-check"></i> Suporte Prioritário</li>
                        </ul>
                        <a href="https://wa.me/5531999999999?text=Quero%20plano%20Profissional" class="btn-buy">Contratar Agora</a>
                    </div>
                </div>

                <!-- Plano Enterprise -->
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="box">
                        <h3>EMPRESARIAL</h3>
                        <h4><sup>R$</sup>149<span> / mês</span></h4>
                        <ul>
                            <li><i class="bi bi-check"></i> <strong>Tudo do Profissional</strong></li>
                            <li><i class="bi bi-check"></i> Usuários Ilimitados</li>
                            <li><i class="bi bi-check"></i> API de Integração</li>
                            <li><i class="bi bi-check"></i> Banco de Dados Dedicado</li>
                            <li><i class="bi bi-check"></i> Personalização de Marca</li>
                        </ul>
                        <a href="https://wa.me/5531999999999?text=Quero%20plano%20Empresarial" class="btn-buy">Falar com Consultor</a>
                    </div>
                </div>

            </div>
        </section>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>