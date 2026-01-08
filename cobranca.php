<?php
/**
 * cobranca.php
 * Painel do Cliente - Tela mínima de cobrança.
 * Função: Exibir mensalidade e status do plano.
 */

session_start();
require_once 'config.php';
require_once 'core/financeiro/FinanceiroService.php';

// Segurança: Apenas Clientes (ou Admin vendo como cliente)
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login_prod.php');
    exit;
}

$service = new FinanceiroService();
$dados = $service->obterResumoFinanceiro($_SESSION['usuario_id']);
$assinatura = $dados['assinatura'];
$ciclo = $dados['ciclo_atual'];

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Minha Assinatura | SGT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f2f5; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card-cobranca { width: 100%; max-width: 400px; border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; }
        .card-header-custom { background: linear-gradient(135deg, #0d6efd, #0a58ca); color: white; padding: 30px; text-align: center; }
        .valor-destaque { font-size: 2.5rem; font-weight: 800; margin: 10px 0; }
        .status-pill { background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 50px; font-size: 0.85rem; backdrop-filter: blur(5px); }
    </style>
</head>
<body>

<div class="card card-cobranca">
    <div class="card-header-custom">
        <h5 class="mb-0 opacity-75 text-uppercase small fw-bold">Plano Atual</h5>
        <h2 class="fw-bold mb-0"><?php echo $assinatura ? htmlspecialchars($assinatura['plano']) : 'Nenhum'; ?></h2>
        
        <?php if ($assinatura): ?>
            <div class="valor-destaque">
                R$ <?php echo number_format($assinatura['valor_mensal'], 2, ',', '.'); ?>
            </div>
            <span class="status-pill">
                <i class="bi bi-check-circle-fill"></i> Ativo
            </span>
        <?php else: ?>
            <div class="py-4">
                <a href="contratar.php" class="btn btn-light fw-bold text-primary px-4 rounded-pill">Contratar Agora</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="card-body p-4 bg-white">
        <?php if ($ciclo): ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted">Fatura Atual</span>
                <span class="fw-bold text-dark"><?php echo $ciclo['competencia']; ?></span>
            </div>
            
            <?php if ($ciclo['status'] === 'pendente'): ?>
                <div class="alert alert-warning border-0 d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                    <div class="small lh-sm">
                        <strong>Pagamento Pendente</strong><br>
                        Vence em breve.
                    </div>
                </div>
                <a href="registrar_pagamento.php?id_ciclo=<?php echo $ciclo['id_ciclo']; ?>" class="btn btn-primary w-100 py-2 rounded-pill fw-bold shadow-sm">
                    Pagar Fatura
                </a>
            <?php else: ?>
                <div class="alert alert-success border-0 d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-4"></i>
                    <div class="small lh-sm">
                        <strong>Tudo certo!</strong><br>
                        Fatura paga.
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <p class="text-center text-muted small mb-0">Nenhuma fatura em aberto.</p>
        <?php endif; ?>
        
        <hr class="my-4 opacity-10">
        
        <div class="text-center">
            <a href="painel.php" class="text-decoration-none text-muted small fw-bold">
                <i class="bi bi-arrow-left"></i> Voltar ao Painel
            </a>
        </div>
    </div>
</div>

</body>
</html>
