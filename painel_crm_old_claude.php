<?php
// painel_crm.php - CRM de Propostas (SGT)

// Segurança
if (file_exists('session_validator.php')) {
    require_once 'session_validator.php';
} else {
    session_start();
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: index.php?msg=sessao_expirada");
        exit;
    }
}
require_once 'db.php';

$id_usuario = $_SESSION['usuario_id'];
$ambiente = isset($_SESSION['ambiente']) ? $_SESSION['ambiente'] : 'producao';
$conn = ($ambiente === 'demo') ? Database::getDemo() : Database::getProd();

// Auto-migração REMOVIDA por segurança e performance.
// A coluna 'fase_crm' deve ser garantida via script de setup ou migração controlada.

// AJAX para mover cards
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    header('Content-Type: application/json');
    if ($_POST['acao'] === 'mover') {
        $id = intval($_POST['id']);
        $fase = $conn->real_escape_string($_POST['fase']);
        $conn->query("UPDATE Propostas SET fase_crm = '$fase' WHERE id_proposta = $id AND id_criador = $id_usuario");
        echo json_encode(array('ok' => true));
    }
    exit;
}

// KPIs
$totalRes = $conn->query("SELECT COUNT(*) as t FROM Propostas WHERE id_criador = $id_usuario");
$total = $totalRes->fetch_assoc()['t'];

$fechadasRes = $conn->query("SELECT COUNT(*) as t FROM Propostas WHERE id_criador = $id_usuario AND fase_crm = 'FECHADA'");
$fechadas = $fechadasRes->fetch_assoc()['t'];

$taxa = $total > 0 ? round(($fechadas / $total) * 100, 1) : 0;

// Kanban
$fases = array('ELABORACAO', 'ENVIADA', 'NEGOCIACAO', 'FECHADA');
$kanban = array();
foreach ($fases as $f) {
    $kanban[$f] = array();
    $where = ($f === 'ELABORACAO') ? "(p.fase_crm = 'ELABORACAO' OR p.fase_crm IS NULL OR p.fase_crm = '')" : "p.fase_crm = '$f'";
    $sql = "SELECT p.id_proposta, p.data_criacao, c.nome_cliente, c.telefone, DATEDIFF(NOW(), p.data_criacao) as dias
            FROM Propostas p LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente 
            WHERE p.id_criador = $id_usuario AND $where ORDER BY p.data_criacao DESC LIMIT 15";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) { $kanban[$f][] = $row; }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT | CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { background: #0a0f1a; color: #e2e8f0; font-family: 'Inter', sans-serif; }
        .glass { background: rgba(17, 24, 39, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.08); }
    </style>
</head>
<body class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <a href="painel.php" class="w-10 h-10 rounded-xl glass flex items-center justify-center text-slate-400 hover:text-white">
                    <i class="ph ph-arrow-left text-lg"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-semibold"><span class="text-blue-400">CRM</span> de Propostas</h1>
                    <p class="text-slate-500 text-sm">Gerencie o fluxo dos seus orçamentos</p>
                </div>
            </div>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="glass rounded-xl p-4 text-center">
                <div class="text-2xl font-bold"><?php echo $total; ?></div>
                <div class="text-xs text-slate-500">Total</div>
            </div>
            <div class="glass rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-green-400"><?php echo $fechadas; ?></div>
                <div class="text-xs text-slate-500">Fechadas</div>
            </div>
            <div class="glass rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-blue-400"><?php echo $taxa; ?>%</div>
                <div class="text-xs text-slate-500">Conversão</div>
            </div>
        </div>

        <!-- Kanban -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <?php
            $config = array(
                'ELABORACAO' => array('cor' => 'slate', 'nome' => 'Elaboração'),
                'ENVIADA' => array('cor' => 'blue', 'nome' => 'Enviada'),
                'NEGOCIACAO' => array('cor' => 'amber', 'nome' => 'Negociação'),
                'FECHADA' => array('cor' => 'green', 'nome' => 'Fechada')
            );
            foreach ($fases as $f): $c = $config[$f];
            ?>
            <div class="glass rounded-xl overflow-hidden">
                <div class="p-3 border-b border-white/10 flex items-center justify-between">
                    <span class="font-medium text-<?php echo $c['cor']; ?>-400"><?php echo $c['nome']; ?></span>
                    <span class="text-xs bg-white/10 px-2 py-0.5 rounded-full"><?php echo count($kanban[$f]); ?></span>
                </div>
                <div class="p-2 space-y-2 max-h-[500px] overflow-y-auto">
                    <?php if (empty($kanban[$f])): ?>
                        <div class="text-center py-8 text-slate-600 text-sm">Vazio</div>
                    <?php else: ?>
                        <?php foreach ($kanban[$f] as $p): 
                            $nome = $p['nome_cliente'] ? $p['nome_cliente'] : 'Sem nome';
                            $tel = preg_replace('/\D/', '', isset($p['telefone']) ? $p['telefone'] : '');
                        ?>
                        <div class="bg-slate-800/50 p-3 rounded-lg border border-white/5 hover:border-white/20 transition-all">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-[10px] text-slate-500">#<?php echo $p['id_proposta']; ?></span>
                                <span class="text-[10px] text-slate-500"><?php echo $p['dias']; ?>d</span>
                            </div>
                            <div class="font-medium text-sm mb-2 truncate"><?php echo $nome; ?></div>
                            <div class="flex gap-1">
                                <?php if ($tel): ?>
                                <a href="https://wa.me/55<?php echo $tel; ?>?text=Oi! Sobre a proposta %23<?php echo $p['id_proposta']; ?>" target="_blank" 
                                   class="flex-1 py-1.5 rounded bg-green-600 hover:bg-green-500 text-white text-[10px] font-medium text-center transition-all">
                                    <i class="ph ph-whatsapp-logo"></i> WhatsApp
                                </a>
                                <?php endif; ?>
                                <a href="gerar_proposta_html.php?id=<?php echo $p['id_proposta']; ?>" target="_blank"
                                   class="flex-1 py-1.5 rounded bg-blue-600/20 hover:bg-blue-600 text-blue-400 hover:text-white text-[10px] font-medium text-center transition-all">
                                    Ver
                                </a>
                                <?php if ($f !== 'FECHADA'): ?>
                                <button onclick="mover(<?php echo $p['id_proposta']; ?>, 'FECHADA')" 
                                        class="px-2 py-1.5 rounded bg-green-600/20 hover:bg-green-600 text-green-400 hover:text-white text-[10px] transition-all">
                                    ✓
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
    function mover(id, fase) {
        fetch('painel_crm.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'acao=mover&id=' + id + '&fase=' + fase
        }).then(function() { location.reload(); });
    }
    </script>
</body>
</html>
