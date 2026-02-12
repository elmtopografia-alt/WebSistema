<?php
// Inicio: ajustar_datas.php
// Função: Laboratório de Ajuste de Datas (Visão Global Agrupada por Criador)

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Segurança Básica
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$ambiente = $_SESSION['ambiente'] ?? 'producao';
$conn = ($ambiente === 'demo') ? Database::getDemo() : Database::getProd();

// 2. Processamento AJAX (Salvar sem reload)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_mode'])) {
    header('Content-Type: application/json');
    
    $id_proposta = intval($_POST['id_proposta']);
    $nova_data = $_POST['nova_data']; 

    if ($id_proposta && $nova_data) {
        $data_sql = str_replace('T', ' ', $nova_data) . ':00';
        
        // Update Direto (Sem trava de dono, pois é modo laboratório)
        $stmt = $conn->prepare("UPDATE Propostas SET data_criacao = ? WHERE id_proposta = ?");
        $stmt->bind_param('si', $data_sql, $id_proposta);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => $conn->error]);
        }
    }
    exit;
}

// 3. Consulta Agrupada por Criador
// Fazemos um JOIN com Usuarios para mostrar o nome do dono, não só o ID
$sql = "SELECT p.id_proposta, p.id_criador, p.nome_cliente_salvo, p.empresa_proponente_nome, p.data_criacao,
               u.nome_completo as nome_criador
        FROM Propostas p
        LEFT JOIN Usuarios u ON p.id_criador = u.id_usuario
        ORDER BY p.id_criador ASC, p.id_proposta DESC";

$result = $conn->query($sql);
$ultimo_criador = -1;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Laboratório de Datas | SGT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #e9ecef; }
        .criador-header {
            background-color: #212529;
            color: #ffc107;
            padding: 10px 15px;
            font-weight: bold;
            font-size: 1.1rem;
            border-top: 4px solid #ffc107;
        }
        .table-input { border: 2px solid #0d6efd; font-weight: bold; border-radius: 4px; color: #0d6efd; }
    </style>
</head>
<body>

    <div class="container-fluid py-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="bi bi-cone-striped me-2"></i>Laboratório de Vendas (Ajuste de Datas)</h4>
            <a href="painel.php" class="btn btn-dark btn-sm">Voltar ao Painel</a>
        </div>

        <div class="card shadow border-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 bg-white">
                    <thead class="table-secondary text-center">
                        <tr>
                            <th width="5%">ID Prop.</th>
                            <th width="5%">ID Criador</th>
                            <th>Cliente</th>
                            <th>Empresa Proponente</th>
                            <th width="15%">Data Atual</th>
                            <th width="20%">Nova Data & Salvar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): 
                            $data_input = date('Y-m-d\TH:i', strtotime($row['data_criacao']));
                            
                            // Lógica de Agrupamento Visual
                            if ($ultimo_criador !== $row['id_criador']):
                        ?>
                            <!-- Cabeçalho do Grupo -->
                            <tr>
                                <td colspan="6" class="criador-header">
                                    <i class="bi bi-person-badge-fill me-2"></i>
                                    CRIADOR ID: <?= $row['id_criador'] ?> - <?= htmlspecialchars($row['nome_criador'] ?? 'Desconhecido') ?>
                                </td>
                            </tr>
                        <?php 
                            $ultimo_criador = $row['id_criador'];
                            endif; 
                        ?>

                        <tr id="row-<?= $row['id_proposta'] ?>">
                            <form onsubmit="salvarData(event, <?= $row['id_proposta'] ?>)">
                                
                                <td class="text-center fw-bold bg-light">
                                    <?= $row['id_proposta'] ?>
                                </td>
                                
                                <td class="text-center text-muted">
                                    <?= $row['id_criador'] ?>
                                </td>
                                
                                <td>
                                    <?= htmlspecialchars($row['nome_cliente_salvo']) ?>
                                </td>
                                
                                <td>
                                    <?= htmlspecialchars($row['empresa_proponente_nome']) ?>
                                </td>
                                
                                <td class="text-center small text-secondary">
                                    <?= date('d/m/Y H:i', strtotime($row['data_criacao'])) ?>
                                </td>
                                
                                <td>
                                    <div class="input-group">
                                        <input type="datetime-local" id="date-<?= $row['id_proposta'] ?>" class="form-control form-control-sm table-input" value="<?= $data_input ?>" required>
                                        <button type="submit" id="btn-<?= $row['id_proposta'] ?>" class="btn btn-primary btn-sm">
                                            <i class="bi bi-save"></i>
                                        </button>
                                    </div>
                                </td>

                            </form>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SCRIPT AJAX -->
    <script>
        function salvarData(e, id) {
            e.preventDefault(); 
            
            const btn = document.getElementById('btn-' + id);
            const inputDate = document.getElementById('date-' + id).value;
            const originalHtml = '<i class="bi bi-save"></i>';

            btn.innerHTML = '...';
            btn.className = 'btn btn-warning btn-sm';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('ajax_mode', '1');
            formData.append('id_proposta', id);
            formData.append('nova_data', inputDate);

            fetch('ajustar_datas.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(d => {
                if(d.status === 'success') {
                    btn.className = 'btn btn-success btn-sm';
                    btn.innerHTML = '<i class="bi bi-check-lg"></i>';
                    setTimeout(() => {
                        btn.className = 'btn btn-primary btn-sm';
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                    }, 1000);
                } else {
                    alert('Erro: ' + d.msg);
                    btn.className = 'btn btn-danger btn-sm';
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                btn.className = 'btn btn-danger btn-sm';
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>
<?php 
// Fim: ajustar_datas.php
?>