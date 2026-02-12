<?php
// robo_start.php
// Interface de Comando do Robô de Prospecção
include 'conexao.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT Robô - Nova Missão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sgt-blue: #0d6efd; 
            --sgt-bg: #f4f6f9;
            --card-glass: rgba(255, 255, 255, 0.95);
        }
        body { background-color: var(--sgt-bg); font-family: 'Segoe UI', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .mission-card {
            background: var(--card-glass);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.8);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            padding: 30px;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
            border-color: var(--sgt-blue);
        }
        .btn-launch {
            background: linear-gradient(45deg, #0d6efd, #0a58ca);
            border: none;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
            transition: all 0.3s ease;
        }
        .btn-launch:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
        }
    </style>
</head>
<body>

    <div class="mission-card">
        <div class="text-center mb-4">
            <h1 class="h3 fw-bold text-primary"><i class="bi bi-robot"></i> Iniciar Missão</h1>
            <p class="text-muted small">Defina os parâmetros para o Robô de Prospecção</p>
        </div>

        <form action="robo_exec.php" method="POST" target="_blank">
            <div class="mb-3">
                <label class="form-label fw-bold small text-uppercase text-muted">Nicho / Termo de Busca</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="termo" class="form-control" placeholder="Ex: Topografia, Engenharia..." value="Topografia" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small text-uppercase text-muted">Localização Alvo</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-geo-alt"></i></span>
                    <input type="text" name="local" class="form-control" placeholder="Ex: São Paulo, SP" value="São Paulo" required>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-6">
                    <label class="form-label fw-bold small text-uppercase text-muted">Profundidade</label>
                    <select name="paginas" class="form-select">
                        <option value="1">1 Página (Rápido)</option>
                        <option value="2" selected>2 Páginas</option>
                        <option value="5">5 Páginas (Completo)</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold small text-uppercase text-muted">Motor</label>
                    <select name="motor" class="form-select">
                        <option value="duck">DuckDuckGo (Livre)</option>
                        <!-- <option value="google">Google API (Pago)</option> -->
                    </select>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-launch rounded-pill">
                    <i class="bi bi-rocket-takeoff"></i> Lançar Robô
                </button>
            </div>
        </form>
    </div>

</body>
</html>
