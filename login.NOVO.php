<?php
/**
 * Login - Versão Nova (Segura)
 * 
 * Substituição para login.php com CSRF e AuthService
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use SGT\Security\AuthService;
use SGT\Security\CSRFProtection;

// Se já está logado, redireciona para o painel antigo por enquanto
if (AuthService::check()) {
    header('Location: painel.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Valida CSRF
        CSRFProtection::verifyOrFail();
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // O AuthService da Fase 3 já faz a verificação PDO segura
        $result = AuthService::login($email, $password);
        
        if ($result['success']) {
            flash('success', 'Bem-vindo de volta!');
            header('Location: painel.php');
            exit;
        } else {
            $error = $result['error'];
        }
        
    } catch (\Exception $e) {
        $error = 'Erro de segurança: ' . $e->getMessage();
    }
}

// Gera token CSRF para o formulário
$csrfToken = CSRFProtection::getToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT - Login Seguro</title>
    <!-- Reaproveitando o estilo SGT que você já tem no projeto -->
    <link rel="stylesheet" href="assets/css/style.css"> 
    <style>
        body { background: #0f172a; color: white; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .login-card { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); padding: 2.5rem; border-radius: 16px; border: 1px solid rgba(255,255,255,0.1); width: 100%; max-width: 400px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); }
        .logo { text-align: center; margin-bottom: 2rem; }
        .logo h2 { margin: 0; font-size: 1.5rem; letter-spacing: 2px; color: #38bdf8; }
        .form-label { display: block; margin-bottom: 0.5rem; font-size: 0.875rem; color: #94a3b8; }
        .form-control { width: 100%; background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 0.75rem; color: white; margin-bottom: 1.25rem; }
        .btn-primary { width: 100%; background: #0284c7; color: white; border: none; border-radius: 8px; padding: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .btn-primary:hover { background: #0369a1; transform: translateY(-1px); }
        .error-msg { background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #f87171; padding: 0.75rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.875rem; text-align: center; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <h2>SGT SYSTEM</h2>
            <p style="color: #64748b; font-size: 0.8rem;">Acesso Administrativo</p>
        </div>

        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <?= CSRFProtection::getInputField() ?>
            
            <label class="form-label" for="email">E-mail</label>
            <input class="form-control" type="email" id="email" name="email" required placeholder="seu@email.com" value="<?= old('email') ?>">

            <label class="form-label" for="password">Senha</label>
            <input class="form-control" type="password" id="password" name="password" required placeholder="••••••••">

            <button type="submit" class="btn-primary">ENTRAR NO SISTEMA</button>
        </form>
        
        <div style="margin-top: 1.5rem; text-align: center; font-size: 0.75rem; color: #475569;">
            © <?= date('Y') ?> ELM Topografia - Segurança Ativa
        </div>
    </div>
</body>
</html>
