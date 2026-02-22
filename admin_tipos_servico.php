<?php
require_once __DIR__ . '/session_validator.php';
require_once __DIR__ . '/ConnectionManager.php';
$conn = ConnectionManager::get();

// Deletar item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['deletar_id'])) {
    $id = (int)$_POST['deletar_id'];
    $conn->query("DELETE FROM tipos_servico WHERE id = $id");
    echo "<script>location.href=''</script>";
    exit;
}

// Listar
$res = $conn->query("SELECT * FROM tipos_servico ORDER BY nome ASC");
$itens = [];
while ($r = $res->fetch_assoc()) $itens[] = $r;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Tipos de Serviço</title>
    <style>
        body { font-family: Inter, sans-serif; background: #0f172a; color: #e2e8f0; padding: 40px; }
        h1 { color: #38bdf8; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; background: #1e293b; border-radius: 10px; overflow: hidden; }
        th { background: #334155; padding: 12px 16px; text-align: left; color: #94a3b8; font-size: 12px; text-transform: uppercase; }
        td { padding: 12px 16px; border-bottom: 1px solid #334155; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; color: white; }
        .btn-del { background: #ef4444; color: white; border: none; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-size: 13px; }
        .btn-del:hover { background: #dc2626; }
        p.info { color: #64748b; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>🗂️ Gerenciar Classificações (Interno)</h1>
    <p class="info">Tabela: <code>tipos_servico</code> — <?= count($itens) ?> itens encontrados. Delete os que não são de topografia.</p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Cor</th>
                <th>Ícone</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itens as $item): ?>
            <tr>
                <td><?= $item['id'] ?></td>
                <td>
                    <span class="badge" style="background: <?= htmlspecialchars($item['cor'] ?? '#666') ?>">
                        <?= htmlspecialchars($item['nome']) ?>
                    </span>
                </td>
                <td><code><?= htmlspecialchars($item['cor'] ?? '-') ?></code></td>
                <td><code><?= htmlspecialchars($item['icone'] ?? '-') ?></code></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Deletar <?= addslashes($item['nome']) ?>?')">
                        <input type="hidden" name="deletar_id" value="<?= $item['id'] ?>">
                        <button class="btn-del" type="submit">🗑️ Deletar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p style="margin-top: 30px; color: #475569; font-size: 13px;">
        ⚠️ Após limpar, delete este arquivo do servidor por segurança.
    </p>
</body>
</html>
