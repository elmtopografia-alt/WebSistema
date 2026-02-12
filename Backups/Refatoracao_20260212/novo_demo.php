<?php
// ARQUIVO: novo_demo.php
// VERSÃO: TEMA AZUL (Isolado do ambiente de produção)
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Gerar Acesso Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Fundo AZUL para reforçar que é DEMO */
        body { background: linear-gradient(135deg, #e3f2fd 0%, #90caf9 100%); height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; }
        .card-demo { max-width: 450px; width: 100%; border-radius: 20px; border: none; box-shadow: 0 15px 40px rgba(13, 110, 253, 0.2); }
        .btn-blue { background-color: #0d6efd; color: white; padding: 12px; font-weight: bold; border-radius: 50px; transition: 0.3s; width: 100%; border: none; }
        .btn-blue:hover { background-color: #0b5ed7; transform: translateY(-2px); }
        .back-link { text-decoration: none; color: #6c757d; font-size: 0.9rem; transition: 0.2s; }
        .back-link:hover { color: #0d6efd; }
    </style>
</head>
<body>
    <div class="card card-demo p-5 text-center">
        <div class="mb-4">
            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-4">
                <i class="fa-solid fa-wand-magic-sparkles text-primary" style="font-size: 3rem;"></i>
            </div>
        </div>
        
        <h3 class="fw-bold text-primary">Acesso Instantâneo</h3>
        <p class="text-muted mb-4 small">Você receberá uma senha provisória na tela agora mesmo. Sem burocracia.</p>
        
        <!-- Aponta para o processador que mostra o cartão com a senha -->
        <form action="processa_demo.php" method="POST">
            <div class="form-floating mb-4 text-start">
                <input type="email" name="email_demo" class="form-control" id="emailInput" placeholder="name@example.com" required>
                <label for="emailInput">Digite seu E-mail</label>
            </div>
            
            <button type="submit" class="btn btn-blue shadow-sm">
                GERAR MINHA SENHA <i class="fa-solid fa-arrow-right ms-2"></i>
            </button>
        </form>

        <div class="mt-4 pt-3 border-top">
            <!-- Volta apenas para o Login Demo, não para o portal geral -->
            <a href="login_demo.php" class="back-link">
                <i class="fa-solid fa-arrow-left me-1"></i> Voltar para Login Demo
            </a>
        </div>
    </div>
</body>
</html>