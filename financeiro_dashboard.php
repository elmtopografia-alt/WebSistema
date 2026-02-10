<?php
// Arquivo: financeiro_dashboard.php
// Objetivo: Painel Financeiro do Cliente com Design Premium e Modo Demo Automático
declare(strict_types=1);

require_once 'session_validator.php';
require_once 'config.php';
require_once 'db.php';

$id_usuario = intval($_SESSION['usuario_id']);
$is_demo_mode = false;

/*
  1) Busca assinatura ativa (Real)
*/
$sql_assinatura = "
    SELECT a.id_assinatura, a.plano, a.valor_mensal, a.status, a.data_inicio
    FROM Assinaturas a
    WHERE a.id_usuario = ?
    ORDER BY a.id_assinatura DESC LIMIT 1
";
$stmt = $conn->prepare($sql_assinatura);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$assinatura = $stmt->get_result()->fetch_assoc();

/*
  2) Lógica de Fallback (Modo Demonstração)
  Se não encontrar assinatura real, gera dados fictícios para o usuário visualizar o potencial.
*/
if (!$assinatura) {
    $is_demo_mode = true;
    $assinatura = [
        'plano' => 'Plano PRO (Demonstração)',
        'valor_mensal' => 99.90,
        'status' => 'ativo',
        'data_inicio' => date('Y-m-d', strtotime('-1 month'))
    ];
}

/*
  3) Busca ou Gera Ciclo Financeiro
*/
$ciclo = null;
$pagamento = null;

if (!$is_demo_mode) {
    // Busca real
    $sql_ciclo = "
        SELECT c.id_ciclo, c.competencia, c.valor_previsto, c.status
        FROM Ciclos_Financeiros c
        WHERE c.id_assinatura = ?
        ORDER BY c.competencia DESC LIMIT 1
    ";
    $stmt = $conn->prepare($sql_ciclo);
    $stmt->bind_param("i", $assinatura['id_assinatura']);
    $stmt->execute();
    $ciclo = $stmt->get_result()->fetch_assoc();
    
    if ($ciclo) {
        $sql_pgto = "SELECT data_pagamento, metodo FROM Pagamentos WHERE id_ciclo = ? LIMIT 1";
        $stmt_pgto = $conn->prepare($sql_pgto);
        $stmt_pgto->bind_param("i", $ciclo['id_ciclo']);
        $stmt_pgto->execute();
        $pagamento = $stmt_pgto->get_result()->fetch_assoc();
    }
} else {
    // Dados Dummy para Demo
    $ciclo = [
        'id_ciclo' => 0,
        'competencia' => date('m/Y'),
        'valor_previsto' => 99.90,
        'status' => 'pendente' // Alternar para testar visual
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Financeiro | SGT</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        /* === SGT DARK THEME CORE === */
        :root {
            --bg-page: #0f172a;
            --bg-card: #1e293b;
            --bg-hover: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --accent: #f59e0b; /* Amber 500 */
            --success: #10b981;
            --danger: #ef4444;
            --border: #334155;
            --glass: rgba(30, 41, 59, 0.7);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            line-height: 1.6;
            padding: 2rem;
            min-height: 100vh;
            background-image: radial-gradient(circle at top right, rgba(59, 130, 246, 0.1), transparent 400px);
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        /* HEADER */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 1.5rem;
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            background: linear-gradient(to right, #fff, #cbd5e1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        /* CARDS */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            backdrop-filter: blur(10px);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border-color: var(--primary);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .icon-box {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .icon-plan { background: rgba(59, 130, 246, 0.1); color: var(--primary); }
        .icon-bill { background: rgba(245, 158, 11, 0.1); color: var(--accent); }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-main);
        }

        /* DATA DISPLAY */
        .data-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .data-row:last-child { border-bottom: none; }

        .data-label { color: var(--text-muted); font-size: 0.875rem; }
        .data-value { font-weight: 500; color: var(--text-main); }
        .data-value.highlight { color: var(--text-main); font-weight: 600; font-size: 1.1rem; }

        /* STATUS BADGES */
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .bg-ativo { background: rgba(16, 185, 129, 0.2); color: var(--success); }
        .bg-pendente { background: rgba(245, 158, 11, 0.2); color: var(--accent); }
        .bg-pago { background: rgba(59, 130, 246, 0.2); color: var(--primary); }
        .bg-cancelado { background: rgba(239, 68, 68, 0.2); color: var(--danger); }

        /* BUTTONS */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            margin-top: 1.5rem;
            cursor: pointer;
            border: none;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
        }
        .btn-primary:hover { filter: brightness(110%); }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text-muted);
        }
        .btn-outline:hover {
            border-color: var(--text-muted);
            color: var(--text-main);
            background: rgba(255,255,255,0.02);
        }

        /* ALERT */
        .alert-demo {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.2);
            color: var(--accent);
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
    </style>
</head>
<body>

<div class="container">
    
    <div class="header">
        <div>
            <h1 class="page-title">Financeiro</h1>
            <p class="subtitle">Gerencie sua assinatura e faturas</p>
        </div>
        <div>
            <a href="painel.php" class="btn-outline" style="padding: 0.5rem 1rem; border-radius: 6px; text-decoration:none; font-size:0.875rem;">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <?php if ($is_demo_mode): ?>
    <div class="alert-demo">
        <i class="bi bi-info-circle-fill"></i>
        <span><b>Modo de Demonstração:</b> Exibindo dados fictícios pois nenhuma assinatura foi encontrada.</span>
    </div>
    <?php endif; ?>

    <div class="grid">
        <!-- CARD PLANO -->
        <div class="card">
            <div class="card-header">
                <div class="icon-box icon-plan"><i class="bi bi-star-fill"></i></div>
                <div class="card-title">Plano Atual</div>
            </div>
            
            <div class="data-row">
                <span class="data-label">Plano Contratado</span>
                <span class="data-value"><?= htmlspecialchars($assinatura['plano']) ?></span>
            </div>
            <div class="data-row">
                <span class="data-label">Valor Mensal</span>
                <span class="data-value highlight">R$ <?= number_format($assinatura['valor_mensal'], 2, ',', '.') ?></span>
            </div>
            <div class="data-row">
                <span class="data-label">Status</span>
                <span class="badge bg-ativo">Ativo</span>
            </div>
            
            <?php if($is_demo_mode): ?>
            <button class="btn btn-primary">
                <i class="bi bi-rocket-takeoff"></i> Fazer Upgrade
            </button>
            <?php endif; ?>
        </div>

        <!-- CARD FATURA ATUAL -->
        <?php if ($ciclo): ?>
        <div class="card">
            <div class="card-header">
                <div class="icon-box icon-bill"><i class="bi bi-receipt"></i></div>
                <div class="card-title">Fatura Atual</div>
            </div>

            <div class="data-row">
                <span class="data-label">Referência</span>
                <span class="data-value"><?= htmlspecialchars($ciclo['competencia']) ?></span>
            </div>
            <div class="data-row">
                <span class="data-label">Valor Total</span>
                <span class="data-value highlight">R$ <?= number_format($ciclo['valor_previsto'], 2, ',', '.') ?></span>
            </div>
            <div class="data-row">
                <span class="data-label">Situação</span>
                <?php 
                    $statusClass = 'bg-cancelado';
                    switch($ciclo['status']) {
                        case 'pago': $statusClass = 'bg-pago'; break;
                        case 'pendente': $statusClass = 'bg-pendente'; break;
                    }
                ?>
                <span class="badge <?= $statusClass ?>"><?= ucfirst($ciclo['status']) ?></span>
            </div>

            <?php if ($ciclo['status'] === 'pago'): ?>
                <div class="data-row" style="margin-top: 1rem; border-top: 1px dashed var(--border);">
                    <span class="data-label">Pago em</span>
                    <span class="data-value"><?= $pagamento ? date('d/m/Y', strtotime($pagamento['data_pagamento'])) : date('d/m/Y') ?></span>
                </div>
                <button class="btn btn-outline" disabled style="opacity: 0.5;">
                    <i class="bi bi-check-lg"></i> Fatura Paga
                </button>
            <?php else: ?>
                <a href="registrar_pagamento.php?id_ciclo=<?= $ciclo['id_ciclo'] ?>" class="btn btn-primary">
                    <i class="bi bi-qr-code"></i> Pagar Agora
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
