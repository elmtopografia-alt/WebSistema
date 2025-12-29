<?php
// ARQUIVO: cadastrar.php
// VERSÃO: TEMA VERDE (Produção Real)
session_start();
$erro = $_SESSION['erro_cadastro'] ?? '';
unset($_SESSION['erro_cadastro']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Novo Cliente | ELM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Fundo VERDE para reforçar que é PRODUÇÃO */
        body { background: linear-gradient(135deg, #d1e7dd 0%, #a3cfbb 100%); height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; }
        .card-register { max-width: 500px; width: 100%; border-radius: 20px; border: none; box-shadow: 0 15px 40px rgba(25, 135, 84, 0.2); }
        .btn-green { background-color: #198754; color: white; padding: 12px; font-weight: bold; border-radius: 50px; transition: 0.3s; width: 100%; border: none; }
        .btn-green:hover { background-color: #146c43; transform: translateY(-2px); }
        .form-control:focus { border-color: #198754; box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25); }
    </style>
</head>
<body>
    <div class="card card-register p-5">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-success"><i class="fa-solid fa-user-shield me-2"></i>Nova Conta</h3>
            <p class="text-muted small">Crie seu acesso definitivo ao sistema.</p>
        </div>

        <?php if($erro): ?>
            <div class="alert alert-danger py-2 small text-center"><?= $erro ?></div>
        <?php endif; ?>

        <form action="processa_cadastro.php" method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary">Nome Completo</label>
                <input type="text" name="nome" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary">E-mail (Login)</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold text-secondary">Senha</label>
                <input type="password" name="senha" class="form-control" required minlength="6">
            </div>
            
            <button type="submit" class="btn btn-green shadow-sm">CONFIRMAR CADASTRO</button>
        </form>

        <div class="mt-4 text-center border-top pt-3">
            <!-- Volta apenas para o Login de Produção -->
            <a href="login_prod.php" class="text-decoration-none small text-success fw-bold">
                <i class="fa-solid fa-arrow-left me-1"></i> Já tenho conta
            </a>
        </div>
    </div>
</body>
</html>