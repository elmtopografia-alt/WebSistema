<?php
// ARQUIVO: forcar_status.php
require_once 'db.php';

// VERIFICA SE O USUÁRIO ENVIOU UMA TROCA DE STATUS
$mensagem = '';
if (isset($_POST['id_proposta']) && isset($_POST['novo_status'])) {
    $id = (int)$_POST['id_proposta'];
    $status = $_POST['novo_status']; // Já vem limpo do select abaixo

    // Atualiza no banco
    $stmt = $conn->prepare("UPDATE Propostas SET status = ? WHERE id_proposta = ?");
    $stmt->bind_param("si", $status, $id);
    
    if ($stmt->execute()) {
        $mensagem = "<div class='alert alert-success'>Sucesso! Proposta #$id alterada para <b>$status</b>.</div>";
    } else {
        $mensagem = "<div class='alert alert-danger'>Erro ao atualizar: " . $conn->error . "</div>";
    }
}

// BUSCA AS ÚLTIMAS 20 PROPOSTAS (Para você achar rápido)
// Se precisar de mais, aumente o LIMIT
$sql = "SELECT id_proposta, numero_proposta, nome_cliente_salvo, valor_final_proposta, status, data_criacao 
        FROM Propostas 
        ORDER BY id_proposta DESC LIMIT 20";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Forçar Status - Emergência</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">

<div class="container">
    <h3 class="mb-4 text-danger">🛠️ Ferramenta de Troca de Status Manual</h3>
    
    <?php echo $mensagem; ?>

    <div class="card shadow">
        <div class="card-header bg-white">
            Últimas Propostas Cadastradas
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID / Nº</th>
                        <th>Cliente</th>
                        <th>Status Atual</th>
                        <th>Ação (Mudar Status)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            #<?php echo $row['id_proposta']; ?> <br>
                            <small class="text-muted"><?php echo $row['numero_proposta']; ?></small>
                        </td>
                        <td>
                            <?php echo $row['nome_cliente_salvo']; ?><br>
                            <small>R$ <?php echo number_format($row['valor_final_proposta'], 2, ',', '.'); ?></small>
                        </td>
                        <td class="fw-bold">
                            <?php echo $row['status']; ?>
                        </td>
                        <td>
                            <form method="POST" class="d-flex gap-2">
                                <input type="hidden" name="id_proposta" value="<?php echo $row['id_proposta']; ?>">
                                
                                <select name="novo_status" class="form-select form-select-sm">
                                    <option value="" selected disabled>Escolha...</option>
                                    <option value="Em Elaboração">🟡 Em Elaboração</option>
                                    <option value="Enviada">🔵 Enviada</option>
                                    <option value="Aprovada">🟢 Aprovada</option>
                                    <option value="Cancelada">🔴 Cancelada</option>
                                </select>
                                
                                <button type="submit" class="btn btn-primary btn-sm">Salvar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>