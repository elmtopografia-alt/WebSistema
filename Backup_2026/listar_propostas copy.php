<?php
// Nome da página: listar_propostas.php
// VERSÃO FINAL - SINCRONIZADA COM A NOVA INTERFACE E LÓGICA DEMO/PROD

session_start();
require_once 'db.php';

// Verificação de Login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// --- CONSULTA ---
// Adicionado filtro por id_criador para segurança no ambiente multi-usuário
$id_criador = $_SESSION['usuario_id'];
$sql = "SELECT p.id_proposta, p.numero_proposta, p.valor_final_proposta, p.data_criacao, p.status, c.nome_cliente
        FROM Propostas p
        LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente
        WHERE p.id_criador = ?
        ORDER BY p.id_proposta DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_criador);
$stmt->execute();
$res = $stmt->get_result();

function getBadge($status) {
    $map = ['Aprovada' => 'success', 'Enviada' => 'primary', 'Recusada' => 'danger', 'Cancelada' => 'secondary', 'Concluída' => 'dark'];
    $cor = $map[$status] ?? 'info'; // 'Em Elaboração' e outros ficam como 'info'
    return "<span class='badge bg-$cor text-uppercase' style='font-size: 0.7em;'>" . htmlspecialchars($status) . "</span>";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Gerenciador de Propostas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 90%;">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fa-solid fa-layer-group text-primary me-2"></i>Gerenciador de Propostas</h3>
        <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar ao Painel</a>
    </div>

    <?php if (isset($_SESSION['mensagem_sucesso'])): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= $_SESSION['mensagem_sucesso'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php unset($_SESSION['mensagem_sucesso']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensagem_erro'])): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= $_SESSION['mensagem_erro'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php unset($_SESSION['mensagem_erro']); ?>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nº Proposta</th>
                            <th>Cliente</th>
                            <th>Valor Final</th>
                            <th>Data</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res && $res->num_rows > 0): while ($row = $res->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($row['numero_proposta']) ?></td>
                                <td><?= htmlspecialchars($row['nome_cliente'] ?? 'N/A') ?></td>
                                <td>R$ <?= number_format($row['valor_final_proposta'], 2, ',', '.') ?></td>
                                <td><?= date('d/m/Y', strtotime($row['data_criacao'])) ?></td>
                                <td class="text-center"><?= getBadge($row['status']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="gerar_documento.php?id=<?= $row['id_proposta'] ?>" class="btn btn-sm btn-outline-primary" title="Gerar Word">
                                            <i class="fa-solid fa-file-word"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="visually-hidden">Mais Ações</span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><h6 class="dropdown-header">Mudar Status Para:</h6></li>
                                            <li><a class="dropdown-item" href="atualizar_status.php?id=<?= $row['id_proposta'] ?>&status=Enviada">Enviada</a></li>
                                            <li><a class="dropdown-item" href="atualizar_status.php?id=<?= $row['id_proposta'] ?>&status=Aprovada">Aprovada</a></li>
                                            <li><a class="dropdown-item" href="atualizar_status.php?id=<?= $row['id_proposta'] ?>&status=Recusada">Recusada</a></li>
                                            <li><a class="dropdown-item" href="atualizar_status.php?id=<?= $row['id_proposta'] ?>&status=Cancelada">Cancelada</a></li>
                                            <li><a class="dropdown-item" href="atualizar_status.php?id=<?= $row['id_proposta'] ?>&status=Concluída">Concluída</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="excluir_proposta.php?id=<?= $row['id_proposta'] ?>" onclick="return confirm('ATENÇÃO: Tem certeza que deseja excluir esta proposta? Esta ação não pode ser desfeita.');">
                                                <i class="fa-solid fa-trash me-2"></i>Excluir Proposta
                                            </a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">Nenhuma proposta encontrada. Comece criando uma!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
*Fim arquivo listar_propostas.php*