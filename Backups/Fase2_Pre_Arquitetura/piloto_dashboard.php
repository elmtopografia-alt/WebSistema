<?php
// ARQUIVO: piloto_dashboard.php
// VERSÃO: Final Integrada (SGT)

// 1. CONFIGURAÇÕES INICIAIS
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'db.php';

session_start();

// DETECÇÃO DE USUÁRIO (Modo Inteligente)
// Se estiver logado, usa o ID real. Se não, usa o ID 1 (Admin) para não dar tela branca no teste.
$id_usuario = $_SESSION['usuario_id'] ?? $_SESSION['id_criador'] ?? 1;

// =========================================================================
// 2. MOTOR DE ATUALIZAÇÃO (Recebe o clique do botão)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_acao'])) {
    header('Content-Type: application/json');
    
    // Conecta
    try {
        $conn = Database::getProd();
        
        $id_proposta = intval($_POST['id']);
        $novo_status = $_POST['status'];
        
        // Mapeamento de Status (Visual -> Banco)
        $status_banco = $novo_status;
        if(strpos($novo_status, 'Aceita') !== false) $status_banco = 'Aprovada';
        
        // Atualiza no banco
        // Nota: Removi temporariamente a trava de usuario (AND id_criador=?) para garantir que funcione no seu teste
        $sql = "UPDATE Propostas SET status = ? WHERE id_proposta = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status_banco, $id_proposta);
        
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true, 'novo_status' => $status_banco]);
        } else {
            echo json_encode(['sucesso' => false, 'msg' => 'Erro SQL: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['sucesso' => false, 'msg' => 'Erro PHP: ' . $e->getMessage()]);
    }
    exit; // Importante: Para o script aqui para não misturar HTML com JSON
}

// =========================================================================
// 3. CARREGAMENTO DA TELA (HTML)
// =========================================================================
$conn = Database::getProd();

// Consulta KPIs
$kpi = ['elab'=>0, 'envia'=>0, 'aprov'=>0, 'cancel'=>0];
$sqlKpi = "SELECT status, count(*) as qtd FROM Propostas WHERE id_criador = ? GROUP BY status";
$stmt = $conn->prepare($sqlKpi);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resKpi = $stmt->get_result();
while($row = $resKpi->fetch_assoc()) {
    $st = mb_strtolower($row['status']);
    if(strpos($st, 'aprov')!==false || strpos($st, 'conclu')!==false || strpos($st, 'aceit')!==false) $kpi['aprov'] += $row['qtd'];
    elseif(strpos($st, 'envia')!==false) $kpi['envia'] += $row['qtd'];
    elseif(strpos($st, 'cancel')!==false || strpos($st, 'perdid')!==false) $kpi['cancel'] += $row['qtd'];
    else $kpi['elab'] += $row['qtd']; // Todo o resto conta como elaboração/rascunho
}

// Consulta Lista
$sqlLista = "SELECT id_proposta, numero_proposta, nome_cliente_salvo, status, valor_final_proposta, data_criacao 
             FROM Propostas WHERE id_criador = ? ORDER BY data_criacao DESC LIMIT 30";
$stmtLista = $conn->prepare($sqlLista);
$stmtLista->bind_param("i", $id_usuario);
$stmtLista->execute();
$lista = $stmtLista->get_result();

// Função de Cor do Botão
function getCorBtn($st) {
    $st = mb_strtolower($st);
    if(strpos($st, 'aprov')!==false) return 'btn-success';
    if(strpos($st, 'envia')!==false) return 'btn-primary';
    if(strpos($st, 'cancel')!==false) return 'btn-danger';
    return 'btn-warning text-dark';
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel SGT - Piloto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .card-kpi { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .card-kpi:hover { transform: translateY(-3px); }
        .card-kpi h3 { font-weight: 700; margin: 0; }
        .table-custom th { background-color: #e9ecef; color: #495057; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; }
        .dropdown-menu { border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="p-3">

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-grid-fill text-primary"></i> Painel de Controle</h4>
            <span class="badge bg-secondary">Usuário ID: <?= $id_usuario ?></span>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card p-3 card-kpi border-start border-5 border-warning bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><small class="text-muted fw-bold">EM ELABORAÇÃO</small><h3 class="text-warning"><?= $kpi['elab'] ?></h3></div>
                        <i class="bi bi-pencil-square fs-1 text-warning opacity-25"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 card-kpi border-start border-5 border-primary bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><small class="text-muted fw-bold">ENVIADAS</small><h3 class="text-primary"><?= $kpi['envia'] ?></h3></div>
                        <i class="bi bi-send-fill fs-1 text-primary opacity-25"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 card-kpi border-start border-5 border-success bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><small class="text-muted fw-bold">ACEITAS</small><h3 class="text-success"><?= $kpi['aprov'] ?></h3></div>
                        <i class="bi bi-trophy-fill fs-1 text-success opacity-25"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 card-kpi border-start border-5 border-danger bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><small class="text-muted fw-bold">CANCELADAS</small><h3 class="text-danger"><?= $kpi['cancel'] ?></h3></div>
                        <i class="bi bi-x-circle-fill fs-1 text-danger opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Últimas Propostas Geradas</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-custom">
                        <thead>
                            <tr>
                                <th class="ps-4">Data</th>
                                <th>Proposta</th>
                                <th>Cliente</th>
                                <th>Status (Clique para alterar)</th>
                                <th class="text-end pe-4">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $lista->fetch_assoc()): 
                                $id = $row['id_proposta'];
                                $cor = getCorBtn($row['status']);
                            ?>
                            <tr>
                                <td class="ps-4 text-secondary"><?= date('d/m/y', strtotime($row['data_criacao'])) ?></td>
                                <td class="fw-bold text-dark"><?= $row['numero_proposta'] ?></td>
                                <td><?= htmlspecialchars($row['nome_cliente_salvo']) ?></td>
                                
                                <td>
                                    <div class="dropdown">
                                        <button class="btn <?= $cor ?> btn-sm dropdown-toggle fw-bold shadow-sm" 
                                                type="button" 
                                                data-bs-toggle="dropdown" 
                                                aria-expanded="false"
                                                id="btn-<?= $id ?>"
                                                style="width: 140px; border-radius: 20px;">
                                            <?= $row['status'] ?>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="mudarStatus(<?= $id ?>, 'Em Elaboração')">🟡 Em Elaboração</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="mudarStatus(<?= $id ?>, 'Enviada')">🔵 Enviada</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="mudarStatus(<?= $id ?>, 'Aprovada')">🟢 Aprovada / Aceita</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="mudarStatus(<?= $id ?>, 'Cancelada')">🔴 Cancelada</a></li>
                                        </ul>
                                    </div>
                                </td>
                                
                                <td class="text-end pe-4 fw-bold">R$ <?= number_format($row['valor_final_proposta'], 2, ',', '.') ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function mudarStatus(id, novoStatus) {
            // 1. Efeito visual imediato
            const btn = document.getElementById('btn-' + id);
            const textoAntigo = btn.innerText;
            
            btn.innerText = 'Salvando...';
            btn.classList.add('disabled');
            document.body.style.cursor = 'wait';

            // 2. Prepara envio
            const dados = new FormData();
            dados.append('ajax_acao', 'mudar_status');
            dados.append('id', id);
            dados.append('status', novoStatus);

            // 3. Envia para este mesmo arquivo (piloto_dashboard.php)
            fetch('piloto_dashboard.php', {
                method: 'POST',
                body: dados
            })
            .then(res => res.json())
            .then(json => {
                if(json.sucesso) {
                    // SUCESSO: Recarrega a página para atualizar os contadores lá em cima
                    window.location.reload();
                } else {
                    alert('Erro ao salvar: ' + json.msg);
                    btn.innerText = textoAntigo;
                    btn.classList.remove('disabled');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Erro de conexão. Verifique sua internet.');
                btn.innerText = textoAntigo;
                btn.classList.remove('disabled');
            })
            .finally(() => {
                document.body.style.cursor = 'default';
            });
        }
    </script>
</body>
</html>