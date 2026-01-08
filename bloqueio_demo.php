<?php
// bloqueio_demo.php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Renove seu Acesso - Gera Proposta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card-offer { max-width: 600px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-radius: 20px; overflow: hidden; }
        .header-offer { background: #0d6efd; color: white; padding: 30px; text-align: center; }
        .price { font-size: 3rem; font-weight: bold; }
        .list-group-item { border: none; padding: 10px 20px; font-size: 1.1rem; }
    </style>
</head>
<body>
    <div class="card card-offer">
        <div class="header-offer">
            <h2>⏳ Seu Período de Teste Encerrou</h2>
            <p class="lead">Mas sua produtividade não precisa parar!</p>
        </div>
        <div class="card-body p-5 text-center">
            <h4 class="mb-4">Desbloqueie o acesso ilimitado agora</h4>
            
            <ul class="list-group list-group-flush text-start mb-4">
                <li class="list-group-item">✅ Propostas ilimitadas</li>
                <li class="list-group-item">✅ Cadastro de clientes ilimitado</li>
                <li class="list-group-item">✅ Sem marca d'água de demonstração</li>
                <li class="list-group-item">✅ Suporte prioritário no WhatsApp</li>
            </ul>

            <div class="d-grid gap-2">
                <a href="contratar.php" class="btn btn-primary btn-lg fw-bold">
                    <i class="bi bi-cart-check-fill"></i> Ver Planos e Contratar
                </a>
                <a href="https://wa.me/5531999999999?text=Ola,%20quero%20contratar%20o%20Gera%20Proposta" class="btn btn-outline-success">
                    <i class="bi bi-whatsapp"></i> Falar com Comercial
                </a>
                <a href="logout.php" class="btn btn-link text-muted mt-2">Sair / Trocar Conta</a>
            </div>
            
            <p class="text-muted mt-3 small">Seus dados de teste serão mantidos por mais 4 dias antes da exclusão definitiva.</p>
        </div>
    </div>
</body>
</html>