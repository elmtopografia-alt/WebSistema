<?php
// ARQUIVO: solicitar_acesso.php (Substitui cadastrar.php)
// FUNÇÃO: Coleta dados do usuário para solicitar acesso (pendente de aprovação)

session_start();
// Se o usuário já estiver logado (e for admin), não tem por que solicitar de novo.
// Mas se for um admin que quer criar conta real, pode ser complexo.
// Vamos permitir a visualização do form de solicitação mesmo se logado, se for o caso.

// Mensagem de erro/sucesso (se houver)
$msg = $_SESSION['feedback_solicitacao'] ?? '';
$msg_type = $_SESSION['feedback_type'] ?? '';
unset($_SESSION['feedback_solicitacao']);
unset($_SESSION['feedback_type']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Solicitar Acesso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f0fdf4 0%, #d1e7dd 100%); height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; }
        .card-register { max-width: 500px; width: 100%; border-radius: 20px; border: none; box-shadow: 0 15px 40px rgba(25, 135, 84, 0.2); }
        .btn-green { background-color: #198754; color: white; padding: 12px; font-weight: bold; border-radius: 50px; transition: 0.3s; width: 100%; border: none; }
        .btn-green:hover { background-color: #146c43; transform: translateY(-2px); }
        .form-control:focus { border-color: #198754; box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25); }
    </style>
</head>
<body>
    <div class="card card-register p-5">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-success"><i class="fa-solid fa-user-plus me-2"></i>Solicitar Acesso</h3>
            <p class="text-muted small">Preencha o formulário para que possamos analisar seu pedido.</p>
        </div>

        <?php if($msg): ?>
            <div class="alert alert-<?= $msg_type ?> py-2 small text-center"><?= $msg ?></div>
        <?php endif; ?>

        <!-- IMPORTANTE: O formulário AGORA aponta para o novo script de processamento -->
        <form action="processa_solicitacao.php" method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary">Nome Completo</label>
                <input type="text" name="nome" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary">E-mail</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold text-secondary">Telefone (opcional)</label>
                <input type="text" name="telefone" class="form-control">
            </div>
            
            <button type="submit" class="btn btn-green shadow-sm">ENVIAR SOLICITAÇÃO</button>
        </form>

        <div class="mt-4 text-center border-top pt-3">
            <a href="login_prod.php" class="text-decoration-none small text-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Voltar para Login
            </a>
        </div>
    </div>
</body>
</html>