<?php
/**
 * ARQUIVO: novo_painel.php
 * OBJETIVO: Dashboard Limpo e Funcional (SGT 2.0)
 * CARACTERÍSTICA: Auto-contido (Backend e Frontend juntos para evitar erros)
 */

require_once 'config.php';
require_once 'db.php';

session_start();

// 1. SEGURANÇA BÁSICA
if (!isset($_SESSION['usuario_id'])) {
    // Se não tiver login, redireciona ou usa um ID fixo para teste se for o caso
    // header("Location: login.php"); exit; 
    // Para facilitar seu teste agora, vou assumir que a sessão existe.
    // Se der erro de login, me avise.
}
$id_usuario = $_SESSION['usuario_id'] ?? $_SESSION['id_criador'] ?? 1; // Fallback para 1 se der erro

// =========================================================================
// 2. MOTOR DE ATUALIZAÇÃO (Backend Invisível)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_acao'])) {
    header('Content-Type: application/json');
    
    if ($_POST['ajax_acao'] === 'mudar_status') {
        $id_proposta = intval($_POST['id']);
        $novo_status = $_POST['status'];
        
        // Conexão
        try {
            $conn = Database::getProd();
            
            // Tradução do Status (Visual -> Banco)
            $status_banco = $novo_status; // Assume igual por padrão
            if(strpos($novo_status, 'Aceita') !== false) $status_banco = 'Aprovada';
            
            $sql = "UPDATE Propostas SET status = ? WHERE id_proposta = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $status_banco, $id_proposta);
            
            if ($stmt->execute()) {
                echo json_encode(['sucesso' => true]);
            } else {
                echo json_encode(['sucesso' => false, 'msg' => 'Erro SQL']);
            }
        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'msg' => $e->getMessage()]);
        }
    }
    exit; // Mata o script aqui para não carregar o HTML na resposta do AJAX
}

// =========================================================================
// 3. CARREGAMENTO DE DADOS (KPIs e Lista)
// =========================================================================
$conn = Database::getProd();

// KPIs
$kpi = ['total'=>0, 'aprov'=>0, 'envia'=>0, 'cancel'=>0, 'elab'=>0];
$sqlKpi = "SELECT status, count(*) as qtd FROM Propostas WHERE id_criador = ? GROUP BY status";
$stmt = $conn->prepare($sqlKpi);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resKpi = $stmt->get_result();
while($row = $resKpi->fetch_assoc()) {
    $st = strtolower($row['status']);
    if(strpos($st, 'aprov')!==false || strpos($st, 'conclu')!==false) $kpi['aprov'] += $row['qtd'];
    elseif(strpos($st, 'envia')!==false) $kpi['envia'] += $row['qtd'];
    elseif(strpos($st, 'cancel')!==false) $kpi['cancel'] += $row['qtd'];
    else $kpi['elab'] += $row['qtd'];
}

// Lista de Propostas
$sqlLista = "SELECT p.*, c.nome_cliente_salvo as cliente_nome 
             FROM Propostas p 
             LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente 
             WHERE p.id_criador = ? 
             ORDER BY p.data_criacao DESC LIMIT 50";
$stmtLista = $conn->prepare($sqlLista);
$stmtLista->bind_param("i", $id_usuario);
$stmtLista->execute();
$lista = $stmtLista->get_result();

// Função Auxiliar de Cores
function getStatusCor($st) {
    $st = strtolower($st);
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
    <title>Novo Painel SGT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f0f2f5; font-family: sans-serif; }
        .card-kpi { border:0; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .card-kpi h3 { font-size: 2.5rem; font-weight: bold; margin: 0; }
        .card-kpi span { font-size: 0.9rem; text-transform: uppercase; color: #6c757d; font-weight: bold; }
        .icon-box { font-size: 2rem; opacity: 0.8; }
    </style>
</head>
<body class="p-4">

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark"><i class="bi bi-speedometer2 text-primary"></i> Painel de Controle</h2>
            <button class="btn btn-outline-secondary btn-sm" onclick="window.location.reload()"><i class="bi bi-arrow-clockwise"></i> Atualizar</button>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card-kpi bg-white border-start border-4 border-warning">
                    <div class="d-flex justify-content-between">
                        <div><span>Em Elaboração</span><h3 class="text-warning"><?= $kpi['elab'] ?></h3></div>
                        <div class="icon-box text-warning"><i class="bi bi-pencil-square"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-kpi bg-white border-start border-4 border-primary">
                    <div class="d-flex justify-content-between">
                        <div><span>Enviadas</span><h3 class="text-primary"><?= $kpi['envia'] ?></h3></div>
                        <div class="icon-box text-primary"><i class="bi bi-send"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-kpi bg-white border-start border-4 border-success">
                    <div class="d-flex justify-content-between">
                        <div><span>Aceitas (Vendas)</span><h3 class="text-success"><?= $kpi['aprov'] ?></h3></div>
                        <div class="icon-box text-success"><i class="bi bi-trophy-fill"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-kpi bg-white border-start border-4 border-danger">
                    <div class="d-flex justify-content-between">
                        <div><span>Canceladas</span><h3 class="text-danger"><?= $kpi['cancel'] ?></h3></div>
                        <div class="icon-box text-danger"><i class="bi bi-x-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Últimas Propostas</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Data</th>
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>Status (Clique para mudar)</th>
                                <th class="text-end pe-4">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $lista->fetch_assoc()): 
                                $cor = getStatusCor($row['status']);
                                $id = $row['id_proposta'];
                            ?>
                            <tr>
                                <td class="ps-4 text-secondary"><?= date('d/m/Y', strtotime($row['data_criacao'])) ?></td>
                                <td class="fw-bold"><?= $row['numero_proposta'] ?></td>
                                <td><?= htmlspecialchars($row['nome_cliente_salvo'] ?? 'Cliente não identificado') ?></td>
                                
                                <td>
                                    <div class="dropdown">
                                        <button class="btn <?= $cor ?> btn-sm dropdown-toggle fw-bold shadow-sm" 
                                                type="button" 
                                                data-bs-toggle="dropdown" 
                                                id="btn-<?= $id ?>">
                                            <?= $row['status'] ?>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="trocarStatus(<?= $id ?>, 'Em Elaboração')">🟡 Em Elaboração</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="trocarStatus(<?= $id ?>, 'Enviada')">🔵 Enviada</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="trocarStatus(<?= $id ?>, 'Aprovada')">🟢 Aceita/Aprovada</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="trocarStatus(<?= $id ?>, 'Cancelada')">🔴 Cancelada</a></li>
                                        </ul>
                                    </div>
                                </td>
                                
                                <td class="text-end pe-4 fw-bold text-dark">R$ <?= number_format($row['valor_final_proposta'], 2, ',', '.') ?></td>
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
        function trocarStatus(id, novoStatus) {
            // 1. Feedback Visual
            let btn = document.getElementById('btn-' + id);
            let textoAntigo = btn.innerText;
            btn.innerText = '...';
            btn.classList.add('disabled');
            document.body.style.cursor = 'wait';

            // 2. Prepara o envio (POST para este mesmo arquivo)
            let dados = new FormData();
            dados.append('ajax_acao', 'mudar_status');
            dados.append('id', id);
            dados.append('status', novoStatus);

            fetch('novo_painel.php', { // Envia para ele mesmo!
                method: 'POST',
                body: dados
            })
            .then(resposta => resposta.json())
            .then(json => {
                if (json.sucesso) {
                    // SUCESSO: Recarrega a página para atualizar os cards lá em cima
                    window.location.reload();
                } else {
                    alert('Erro no banco: ' + json.msg);
                    btn.innerText = textoAntigo;
                    btn.classList.remove('disabled');
                }
            })
            .catch(erro => {
                console.error(erro);
                alert('Erro de conexão.');
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