<?php
// listar_propostas.php
session_start();
require_once 'db.php';
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$uid = $_SESSION['usuario_id'];
$res = $conn->query("SELECT * FROM Propostas WHERE id_criador = $uid ORDER BY id_proposta DESC");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Listar Propostas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h3>Todas as Propostas</h3>
        <a href="index.php" class="btn btn-secondary mb-3">Voltar</a>
        <table class="table table-striped bg-white">
            <thead><tr><th>Nº</th><th>Cliente</th><th>Valor</th><th>Status</th><th>Ações</th></tr></thead>
            <tbody>
                <?php while($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['numero_proposta'] ?></td>
                        <td><?= htmlspecialchars($row['nome_cliente_salvo']) ?></td>
                        <td>R$ <?= number_format($row['valor_final_proposta'], 2, ',', '.') ?></td>
                        <td><?= $row['status'] ?></td>
                        <td>
                            <a href="gerar_documento.php?id=<?= $row['id_proposta'] ?>" class="btn btn-sm btn-primary">Word</a>
                            <a href="atualizar_status.php?id=<?= $row['id_proposta'] ?>&status=Aprovada" class="btn btn-sm btn-success">Aprovar</a>
                            <a href="excluir_proposta.php?id=<?= $row['id_proposta'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Excluir?')">X</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>