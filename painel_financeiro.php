<?php
/**
 * painel_financeiro.php
 * Painel Financeiro Integrado - Redesign
 */

session_start();
require_once 'config.php';
require_once 'core/financeiro/FinanceiroService.php';

// Segurança: Apenas ADMIN
if (!isset($_SESSION['usuario_id']) || $_SESSION['perfil'] !== 'admin') {
    header('Location: login_admin.php?redirect=painel_financeiro.php');
    exit;
}

$service = new FinanceiroService();
$dados = $service->obterResumoFinanceiro($_SESSION['usuario_id']);
$assinatura = $dados['assinatura'];

// Mock Data para a tabela conforme solicitado
$mockTransacoes = [];

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Financeiro (Admin) | SGT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f3f4f6; font-family: 'Inter', sans-serif; color: #1f2937; }
        
        /* Card Styles */
        .card-financeiro { 
            border: none; 
            border-radius: 16px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); 
            background: white;
            overflow: hidden;
            transition: transform 0.2s;
        }
        
        .card-header-custom {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 1.5rem;
        }

        /* Typography */
        h2 { font-weight: 700; letter-spacing: -0.025em; color: #111827; }
        h5 { font-weight: 600; color: #374151; }
        
        /* Table Styles */
        .table-custom th { 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            color: #6b7280; 
            font-weight: 600; 
            letter-spacing: 0.05em;
            background-color: #f9fafb;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .table-custom td { 
            padding: 1rem 1.5rem; 
            vertical-align: middle; 
            border-bottom: 1px solid #f3f4f6;
            font-size: 0.875rem;
        }
        .table-custom tr:last-child td { border-bottom: none; }
        
        .id-badge { color: #6b7280; font-family: monospace; font-weight: 500; background: #f3f4f6; padding: 2px 6px; border-radius: 4px; }
        .comprador-nome { font-weight: 500; color: #111827; }
        
        /* Status Badges */
        .badge-status { padding: 0.35em 0.8em; border-radius: 9999px; font-weight: 600; font-size: 0.75rem; }
        .bg-aprovado { background-color: #d1fae5; color: #065f46; }
        .bg-pendente { background-color: #fef3c7; color: #92400e; }
        .bg-cancelado { background-color: #fee2e2; color: #991b1b; }

        /* Buttons */
        .btn-contratar {
            background-color: #2563eb;
            color: white;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.2s;
            border: none;
            width: 100%;
        }
        .btn-contratar:hover {
            background-color: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
        
        .btn-pdf {
            color: #4b5563;
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            transition: all 0.2s;
            text-decoration: none;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-pdf:hover { background-color: #f9fafb; color: #111827; border-color: #d1d5db; }

        /* Search Bar */
        .search-input {
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            padding: 0.6rem 1rem 0.6rem 2.5rem;
            font-size: 0.875rem;
            width: 250px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239CA3AF'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 0.75rem center;
            background-size: 1rem;
        }
        .search-input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
    </style>
</head>
<body>

<div class="container py-5">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="mb-1">Painel Financeiro (Admin)</h2>
            <p class="text-muted mb-0">Gerencie pagamentos e assinaturas</p>
        </div>
        <div>
            <button type="button" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#modalLogs">
                <i class="bi bi-list-ul me-2"></i>Logs MP
            </button>
            <a href="painel.php" class="btn btn-outline-secondary px-4 fw-medium"><i class="bi bi-arrow-left me-2"></i> Voltar ao Painel</a>
        </div>
    </div>

    <!-- Modal Logs -->
    <div class="modal fade" id="modalLogs" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Logs do Mercado Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Data</th>
                                    <th>Evento</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="listaLogs">
                                <tr><td colspan="4" class="text-center">Carregando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('modalLogs').addEventListener('show.bs.modal', function () {
            fetch('api_logs_mp.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('listaLogs');
                    tbody.innerHTML = '';
                    if(data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="4" class="text-center">Nenhum log encontrado.</td></tr>';
                        return;
                    }
                    data.forEach(log => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${log.id_log}</td>
                                <td>${log.data_recebimento}</td>
                                <td>${log.tipo_evento}</td>
                                <td><span class="badge bg-secondary">${log.status_processamento}</span></td>
                            </tr>
                        `;
                    });
                });
        });
    </script>

    <div class="row g-4">
        <!-- Coluna Esquerda: Resumo do Plano -->
        <div class="col-lg-4">
            <div class="card card-financeiro h-100">
                <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-blue-50 rounded-circle mb-3" style="width: 64px; height: 64px; background: #eff6ff;">
                            <i class="bi bi-star-fill text-primary fs-3"></i>
                        </div>
                        <h5 class="text-muted text-uppercase small fw-bold mb-2">Plano Atual</h5>
                        <?php if ($assinatura): ?>
                            <h3 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($assinatura['plano']); ?></h3>
                            <div class="my-3">
                                <span class="display-6 fw-bold text-dark">R$ <?php echo number_format($assinatura['valor_mensal'], 2, ',', '.'); ?></span>
                                <span class="text-muted">/mês</span>
                            </div>
                            <div class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill mb-4">
                                <i class="bi bi-check-circle-fill me-1"></i> Assinatura Ativa
                            </div>
                        <?php else: ?>
                            <h3 class="text-dark mb-3">Nenhum plano ativo</h3>
                            <p class="text-muted mb-4">Escolha um plano para desbloquear todos os recursos.</p>
                            <a href="contratar.php" class="btn btn-contratar">
                                Contratar Agora
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Direita: Histórico -->
        <div class="col-lg-8">
            <div class="card card-financeiro h-100">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Histórico de Pagamentos</h5>
                    <input type="text" class="search-input" placeholder="Buscar por ID ou Nome...">
                </div>
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Data</th>
                                <th>Comprador</th>
                                <th>Método</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Recibo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mockTransacoes)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        Nenhum pagamento registrado no histórico.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($mockTransacoes as $t): 
                                    $statusClass = '';
                                    $statusIcon = '';
                                    switch($t['status']) {
                                        case 'Aprovado': $statusClass = 'bg-aprovado'; $statusIcon = 'bi-check-lg'; break;
                                        case 'Pendente': $statusClass = 'bg-pendente'; $statusIcon = 'bi-clock'; break;
                                        case 'Cancelado': $statusClass = 'bg-cancelado'; $statusIcon = 'bi-x-lg'; break;
                                    }
                                ?>
                                <tr>
                                    <td><span class="id-badge"><?= $t['id'] ?></span></td>
                                    <td class="text-muted"><?= $t['data'] ?></td>
                                    <td><div class="comprador-nome"><?= $t['comprador'] ?></div></td>
                                    <td class="text-muted"><?= $t['metodo'] ?></td>
                                    <td class="fw-bold text-dark">R$ <?= number_format($t['valor'], 2, ',', '.') ?></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm dropdown-toggle badge-status <?= $statusClass ?> border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="btn-status-<?= $t['id'] ?>">
                                                <i class="bi <?= $statusIcon ?> me-1"></i> <?= $t['status'] ?>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="alterarStatus('<?= $t['id'] ?>', 'Aprovado')"><i class="bi bi-check-lg text-success me-2"></i>Aprovado</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="alterarStatus('<?= $t['id'] ?>', 'Pendente')"><i class="bi bi-clock text-warning me-2"></i>Pendente</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="alterarStatus('<?= $t['id'] ?>', 'Inadimplente')"><i class="bi bi-exclamation-circle text-danger me-2"></i>Inadimplente</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item" href="#" onclick="alterarStatus('<?= $t['id'] ?>', 'Cancelado')"><i class="bi bi-x-lg text-secondary me-2"></i>Cancelado</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="ver_recibo.php?id_pagamento=<?= $t['id'] ?>" target="_blank" class="btn-pdf">
                                            <i class="bi bi-file-earmark-pdf"></i> Ver Recibo
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function alterarStatus(id, novoStatus) {
        // Mapeamento de estilos
        const estilos = {
            'Aprovado': { class: 'bg-aprovado', icon: 'bi-check-lg' },
            'Pendente': { class: 'bg-pendente', icon: 'bi-clock' },
            'Inadimplente': { class: 'bg-inadimplente', icon: 'bi-exclamation-circle' },
            'Cancelado': { class: 'bg-cancelado', icon: 'bi-x-lg' }
        };

        const config = estilos[novoStatus];
        if (!config) return;

        // Atualiza o botão
        const btn = document.getElementById('btn-status-' + id);
        
        // Remove classes antigas de cor
        btn.classList.remove('bg-aprovado', 'bg-pendente', 'bg-cancelado', 'bg-inadimplente');
        
        // Adiciona nova classe
        btn.classList.add(config.class);
        
        // Atualiza texto e ícone
        btn.innerHTML = `<i class="bi ${config.icon} me-1"></i> ${novoStatus}`;
    }
</script>

<style>
    /* Adicionando estilo para Inadimplente */
    .bg-inadimplente { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
</style>

</body>
</html>
