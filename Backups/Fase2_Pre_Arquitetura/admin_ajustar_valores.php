<?php
// ARQUIVO: admin_ajustar_valores.php
// OBJETIVO: Listar TODAS as propostas (Raw View) e permitir ajuste de valores.

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Segurança Básica
if (!isset($_SESSION['usuario_id'])) {
    die("Acesso negado. Faça login.");
}

$conn = Database::getProd();

// 2. Processar Atualização (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'atualizar_valor') {
    header('Content-Type: application/json');
    
    $id_proposta = intval($_POST['id_proposta']);
    $valor_raw = $_POST['novo_valor'];
    
    // Limpeza de valor (pt-BR -> float)
    $valor_clean = str_replace('.', '', $valor_raw);
    $valor_clean = str_replace(',', '.', $valor_clean);
    $valor_final = floatval($valor_clean);

    $stmt = $conn->prepare("UPDATE Propostas SET valor_final_proposta = ? WHERE id_proposta = ?");
    $stmt->bind_param('di', $valor_final, $id_proposta);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'msg' => 'Valor atualizado!', 'valor_formatado' => number_format($valor_final, 2, ',', '.')]);
    } else {
        echo json_encode(['success' => false, 'msg' => 'Erro SQL: ' . $stmt->error]);
    }
    exit;
}

// 3. Buscar Dados (Lista Simples)
$sql = "SELECT * FROM Propostas ORDER BY id_criador ASC, id_proposta DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Ajustar Valores (Lista Completa)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; padding: 20px; }
        .table-sm td { vertical-align: middle; font-size: 0.9rem; }
        .input-valor { width: 130px; text-align: right; font-weight: bold; }
        .col-id { width: 60px; text-align: center; }
        .col-criador { width: 80px; text-align: center; background-color: #e9ecef; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>🛠️ Ajuste de Valores (Banco de Dados Completo)</h3>
            <a href="painel.php" class="btn btn-secondary">Voltar ao Painel</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-bordered table-hover table-sm mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="col-criador">ID Criador</th>
                            <th class="col-id">ID Prop.</th>
                            <th>Data</th>
                            <th>Número</th>
                            <th>Cliente</th>
                            <th class="text-end">Valor (R$)</th>
                            <th class="text-center">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="col-criador"><?= $row['id_criador'] ?></td>
                                <td class="col-id"><?= $row['id_proposta'] ?></td>
                                <td><?= date('d/m/Y', strtotime($row['data_criacao'])) ?></td>
                                <td><?= htmlspecialchars($row['numero_proposta']) ?></td>
                                <td><?= htmlspecialchars($row['nome_cliente_salvo']) ?></td>
                                <td class="text-end">
                                    <input type="text" class="form-control form-control-sm input-valor d-inline-block" 
                                           id="val-<?= $row['id_proposta'] ?>"
                                           value="<?= number_format($row['valor_final_proposta'], 2, ',', '.') ?>" 
                                           onfocus="this.select()">
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-primary btn-sm" onclick="salvarValor(<?= $row['id_proposta'] ?>)">
                                        💾 Salvar
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center p-4">Nenhuma proposta encontrada.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function salvarValor(id) {
            const input = document.getElementById('val-' + id);
            const novoValor = input.value;
            const btn = event.target;
            const originalText = btn.innerText;

            btn.innerText = '...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('acao', 'atualizar_valor');
            formData.append('id_proposta', id);
            formData.append('novo_valor', novoValor);

            fetch('admin_ajustar_valores.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    btn.className = 'btn btn-success btn-sm';
                    btn.innerText = '✅';
                    setTimeout(() => {
                        btn.className = 'btn btn-primary btn-sm';
                        btn.innerText = originalText;
                    }, 1500);
                    if(res.valor_formatado) input.value = res.valor_formatado;
                } else {
                    alert('Erro: ' + res.msg);
                    btn.innerText = originalText;
                }
            })
            .catch(err => {
                console.error(err);
                alert('Erro de conexão.');
                btn.innerText = originalText;
            })
            .finally(() => {
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>
