<?php
// Nome do Arquivo: sucesso_contratacao.php
// Função: Página de Agradecimento pós-pagamento. Acalma o cliente e direciona para o WhatsApp.

require_once 'config.php';

// Número do Admin para agilizar a liberação
$whatsapp_comercial = "5531971875928"; // Coloque seu número real aqui
$msg_zap = "Olá! Acabei de realizar o pagamento da assinatura do SGT. Poderia liberar meu acesso?";
$link_zap = "https://api.whatsapp.com/send?phone=$whatsapp_comercial&text=" . urlencode($msg_zap);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Confirmado! | <?php echo defined('SITE_NAME') ? SITE_NAME : 'SGT'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
        .card-success { border: none; border-radius: 15px; box-shadow: 0 15px 40px rgba(0,0,0,0.1); max-width: 600px; width: 95%; background: white; text-align: center; padding: 40px; }
        .icon-box { font-size: 5rem; color: #198754; margin-bottom: 20px; animation: pop 0.5s ease; }
        @keyframes pop { 0% { transform: scale(0); } 80% { transform: scale(1.1); } 100% { transform: scale(1); } }
    </style>
</head>
<body>

    <div class="card-success">
        <div class="icon-box">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        
        <h2 class="fw-bold text-success mb-3">Pagamento Recebido!</h2>
        <p class="lead text-muted">Obrigado por confiar no SGT.</p>
        
        <hr class="my-4">
        
        <div class="alert alert-info text-start border-0 bg-opacity-10 bg-info">
            <h5 class="fw-bold text-info mb-2"><i class="bi bi-info-circle-fill me-2"></i>Próximos Passos:</h5>
            <ol class="mb-0 ps-3">
                <li class="mb-2">Nossa equipe financeira já foi notificada.</li>
                <li class="mb-2">Estamos preparando seu ambiente de Produção exclusivo.</li>
                <li>Você receberá seu <strong>Login e Senha</strong> no e-mail cadastrado (ou WhatsApp) em instantes.</li>
            </ol>
        </div>

        <p class="mt-4 mb-3 text-muted">Quer agilizar sua ativação?</p>
        
        <a href="<?php echo $link_zap; ?>" target="_blank" class="btn btn-success btn-lg w-100 fw-bold shadow-sm">
            <i class="bi bi-whatsapp me-2"></i> AVISAR QUE PAGUEI
        </a>
        
        <div class="mt-3">
            <a href="index.php" class="text-decoration-none text-muted small">Voltar ao início</a>
        </div>
    </div>

</body>
</html>