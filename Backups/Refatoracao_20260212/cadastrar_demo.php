<?php
// ARQUIVO: cadastrar_demo.php
// VERSÃO: FOCADA EM CRIAR/RECUPERAR SENHA PARA PROXIMO ACESSO
session_start();

$email_sugerido = isset($_COOKIE['elm_demo_tracker']) ? htmlspecialchars(base64_decode($_COOKIE['elm_demo_tracker'])) : '';

$mensagem_aviso = "Ao acessar, você receberá um <strong>Login e Senha temporários</strong> para seus próximos acessos. Não esqueça de anotá-los!";

if ($email_sugerido) {
    $mensagem_aviso = "Detectamos seu último acesso. Digite seu e-mail para **receber uma nova senha temporária** e manter seu prazo de teste.";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Iniciar Demonstração</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card-demo { max-width: 450px; width: 100%; border-radius: 15px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="card card-demo p-4">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary">🚀 Teste Grátis (5 Dias)</h3>
            <p class="text-muted small"><?= $mensagem_aviso ?></p>
        </div>
        
        <form action="processa_demo.php" method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold">Seu E-mail (Será seu Login)</label>
                <input type="email" name="email_demo" class="form-control form-control-lg" 
                       placeholder="exemplo@empresa.com" 
                       value="<?= $email_sugerido ?>"
                       required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Acessar Sistema Demo</button>
            </div>
            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none small text-muted">Voltar para o Portal</a>
            </div>
        </form>
    </div>
</body>
</html>