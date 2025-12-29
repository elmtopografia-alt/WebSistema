<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT | Experience</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Fonte Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;800;900&display=swap" rel="stylesheet">

    <!-- GSAP (Motor de Animação) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

    <style>
        :root { --bg-color: #0a0a0a; --text-color: #ffffff; }

        body, html { margin: 0; padding: 0; font-family: 'Inter', sans-serif; background-color: var(--bg-color); color: var(--text-color); overflow-x: hidden; }

        /* PRELOADER (A CORTINA PRETA) */
        .preloader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: #000; z-index: 9999;
            display: flex; justify-content: center; align-items: center;
        }
        
        .loader-text {
            font-size: 8vw; font-weight: 900; letter-spacing: -5px; color: transparent;
            -webkit-text-stroke: 2px white; opacity: 0;
        }

        /* HERO SECTION */
        .hero-section { position: relative; height: 100vh; overflow: hidden; display: flex; justify-content: center; align-items: center; }

        .hero-bg {
            position: absolute; top: -10%; left: -10%; width: 120%; height: 120%;
            background: url('https://images.unsplash.com/photo-1503387762-592deb58ef4e?q=80&w=2531&auto=format&fit=crop') no-repeat center center/cover;
            filter: brightness(0.6) grayscale(100%); z-index: 1;
        }

        /* O VIDRO */
        .hero-glass {
            position: relative; z-index: 10;
            background: rgba(20, 20, 20, 0.4);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 4rem 6rem; text-align: center; max-width: 1200px;
            box-shadow: 0 50px 100px rgba(0,0,0,0.5);
            opacity: 0; transform: translateY(100px);
        }

        h1.display-title {
            font-size: 7vw; line-height: 0.9; font-weight: 900; letter-spacing: -0.05em; margin-bottom: 20px;
            background: linear-gradient(to bottom, #fff, #aaa); -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        .subtitle { font-size: 1.5rem; font-weight: 300; color: #ccc; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 40px; display: block; }

        .btn-swiss {
            padding: 20px 50px; font-size: 1.2rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
            border: 1px solid white; background: transparent; color: white; text-decoration: none;
            transition: all 0.4s; display: inline-block; margin: 10px;
        }
        .btn-swiss.filled { background: white; color: black; border-color: white; }
        .btn-swiss:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(255,255,255,0.2); }

        .content-section { background: #0a0a0a; padding: 100px 0; position: relative; z-index: 5; }

        @media (max-width: 768px) {
            .hero-glass { padding: 2rem; width: 90%; }
            h1.display-title { font-size: 15vw; }
            .btn-swiss { width: 100%; padding: 15px 0; }
        }
    </style>
</head>
<body>

    <div class="preloader">
        <div class="loader-text">SGT 2025</div>
    </div>

    <div class="hero-section">
        <div class="hero-bg"></div>
        <div class="hero-glass">
            <span class="subtitle">Gestão Topográfica</span>
            <h1 class="display-title">PRECISÃO<br>ABSOLUTA</h1>
            <div class="mt-5">
                <a href="criar_conta_demo.php" class="btn-swiss filled">Testar Grátis</a>
                <a href="login.php" class="btn-swiss">Área do Cliente</a>
            </div>
        </div>
    </div>

    <div class="content-section">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <h2 class="fw-bold mb-4 display-6">Engenharia de Software</h2>
                    <p class="lead text-secondary">
                        Um sistema desenvolvido para eliminar o erro humano e maximizar o lucro operacional.
                        Banco de dados seguro, cálculos automáticos e geração de documentos ABNT.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT DA ANIMAÇÃO (LENTO E SUAVE) -->
    <script>
        gsap.registerPlugin(ScrollTrigger);
        const tl = gsap.timeline();

        // 1. Texto aparece (2s)
        tl.to(".loader-text", { opacity: 1, duration: 2, ease: "power2.out" })
          
          // 2. Texto fica parado lendo (2s)
          .to(".loader-text", { opacity: 0, duration: 2, delay: 2 })
          
          // 3. A CORTINA SOBE (5 SEGUNDOS - ULTRA SLOW)
          .to(".preloader", {
              yPercent: -100, 
              duration: 5,    
              ease: "power2.inOut" // Movimento constante e suave
          })
          
          // 4. O Vidro aparece durante a subida da cortina
          .to(".hero-glass", {
              y: 0,
              opacity: 1,
              duration: 4, 
              ease: "power3.out"
          }, "-=4.0"); // Começa junto com o final da cortina

        // Parallax suave
        gsap.to(".hero-bg", {
            yPercent: 30, ease: "none",
            scrollTrigger: { trigger: ".hero-section", start: "top top", end: "bottom top", scrub: true }
        });
    </script>
</body>
</html>