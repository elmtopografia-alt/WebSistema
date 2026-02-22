<?php
/**
 * sgt_mnt.php — Acesse com ?token=SGT2026MNT
 * Corrige campos vazios nas propostas buscando DadosEmpresa e Clientes.
 * DELETE após uso!
 */

$TOKEN = 'SGT2026MNT';
if (($_GET['token'] ?? '') !== $TOKEN) {
    http_response_code(403);
    die('Token inválido. Use ?token=SGT2026MNT');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$confirmar = isset($_GET['ok']) && $_GET['ok'] == '1';
$de_id     = (int)($_GET['de'] ?? 1);

$sql = "SELECT p.id_proposta, p.numero_proposta, p.id_cliente, p.id_criador,
               p.empresa_proponente_nome, p.nome_cliente_salvo, p.email_salvo
        FROM Propostas p
        WHERE p.id_proposta >= $de_id
          AND (
              (p.empresa_proponente_nome IS NULL OR p.empresa_proponente_nome = '')
              OR (p.nome_cliente_salvo IS NULL OR p.nome_cliente_salvo = '')
              OR (p.email_salvo IS NULL OR p.email_salvo = '')
          )
        ORDER BY p.id_proposta ASC";

$propostas   = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
$cacheEmp    = [];
$cacheCli    = [];
$resultados  = [];
$totalFix    = 0;
$totalErro   = 0;

foreach ($propostas as $p) {
    $id  = $p['id_proposta'];
    $idc = (int)$p['id_criador'];
    $idl = (int)$p['id_cliente'];
    $campos = [];
    $log    = [];

    // Empresa Proponente
    if (empty($p['empresa_proponente_nome'])) {
        if (!isset($cacheEmp[$idc])) {
            $cacheEmp[$idc] = $conn->query("SELECT * FROM DadosEmpresa WHERE id_criador=$idc LIMIT 1")->fetch_assoc();
        }
        $emp = $cacheEmp[$idc];
        if ($emp) {
            $campos['empresa_proponente_nome']     = $conn->real_escape_string($emp['Empresa']  ?? '');
            $campos['empresa_proponente_cnpj']     = $conn->real_escape_string($emp['CNPJ']     ?? '');
            $campos['empresa_proponente_endereco'] = $conn->real_escape_string($emp['Endereco'] ?? '');
            $campos['empresa_proponente_cidade']   = $conn->real_escape_string($emp['Cidade']   ?? '');
            $campos['empresa_proponente_estado']   = $conn->real_escape_string($emp['Estado']   ?? '');
            $campos['empresa_proponente_banco']    = $conn->real_escape_string($emp['Banco']    ?? '');
            $campos['empresa_proponente_agencia']  = $conn->real_escape_string($emp['Agencia']  ?? '');
            $campos['empresa_proponente_conta']    = $conn->real_escape_string($emp['Conta']    ?? '');
            $campos['empresa_proponente_pix']      = $conn->real_escape_string($emp['PIX']      ?? '');
            $log[] = 'Empresa: ' . ($emp['Empresa'] ?? 'N/A');
        } else {
            $log[] = 'SEM DadosEmpresa para criador #' . $idc;
        }
    }

    // Dados do Cliente
    if (empty($p['nome_cliente_salvo']) || empty($p['email_salvo'])) {
        if ($idl > 0) {
            if (!isset($cacheCli[$idl])) {
                $cacheCli[$idl] = $conn->query("SELECT nome_cliente,empresa,email,telefone,celular FROM Clientes WHERE id_cliente=$idl")->fetch_assoc();
            }
            $cli = $cacheCli[$idl];
            if ($cli) {
                if (empty($p['nome_cliente_salvo']))
                    $campos['nome_cliente_salvo']  = $conn->real_escape_string($cli['nome_cliente'] ?? '');
                if (empty($p['email_salvo']))
                    $campos['email_salvo']         = $conn->real_escape_string($cli['email'] ?? '');
                $campos['telefone_salvo']          = $conn->real_escape_string($cli['telefone'] ?? '');
                $campos['empresa_cliente_salvo']   = $conn->real_escape_string($cli['empresa']  ?? '');
                $campos['celular_salvo']           = $conn->real_escape_string($cli['celular']  ?? '');
                $campos['whatsapp_salvo']          = $conn->real_escape_string($cli['celular']  ?? '');
                $log[] = 'Cliente: ' . ($cli['nome_cliente'] ?? 'N/A');
            } else {
                $log[] = 'Cliente #' . $idl . ' nao encontrado';
            }
        } else {
            $log[] = 'Sem id_cliente vinculado';
        }
    }

    $status = 'nada a fazer';
    if (!empty($campos)) {
        $set    = implode(', ', array_map(fn($k,$v) => "$k='$v'", array_keys($campos), $campos));
        $sqlUpd = "UPDATE Propostas SET $set WHERE id_proposta=$id";
        if ($confirmar) {
            $status = $conn->query($sqlUpd) ? 'ATUALIZADO' : 'ERRO: '.$conn->error;
            $status === 'ATUALIZADO' ? $totalFix++ : $totalErro++;
        } else {
            $status = 'PREVIEW ('.count($campos).' campos)';
        }
    }

    $resultados[] = ['id'=>$id,'num'=>$p['numero_proposta'],'log'=>implode(' | ',$log),'st'=>$status];
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>SGT Mnt</title>
<style>
body{font-family:monospace;background:#0f172a;color:#e2e8f0;padding:20px;font-size:13px;}
h2{color:#38bdf8;}table{border-collapse:collapse;width:100%;margin-top:14px;}
th{background:#1e293b;color:#94a3b8;padding:7px 12px;text-align:left;}
td{padding:6px 12px;border-bottom:1px solid #1e293b;}
.ok{color:#10b981;font-weight:bold;}.err{color:#ef4444;font-weight:bold;}
.warn{color:#f97316;}.info{color:#a78bfa;}
.btn{display:inline-block;padding:10px 22px;border-radius:8px;text-decoration:none;font-weight:bold;margin:6px 4px;}
.g{background:#16a34a;color:#fff;}.gr{background:#334155;color:#e2e8f0;}
</style></head><body>
<h2>🔧 SGT — Correcao de Dados em Massa</h2>
<p>Propostas com campos vazios a partir do ID <strong>#<?= $de_id ?></strong>: <strong><?= count($propostas) ?></strong></p>

<?php if (!$confirmar): ?>
<div style="background:#1e293b;border:1px solid #f97316;border-radius:8px;padding:14px;margin-bottom:14px;">
    <p class="warn">⚠️ <strong>PREVIEW</strong> — Nenhuma alteração feita ainda. Confira a tabela abaixo e clique em Aplicar.</p>
    <a class="btn g" href="?token=<?= $TOKEN ?>&ok=1&de=<?= $de_id ?>">✅ Aplicar Correcoes</a>
    <a class="btn gr" href="painel.php">Cancelar</a>
</div>
<?php else: ?>
<div style="background:#1e293b;border:1px solid #10b981;border-radius:8px;padding:14px;margin-bottom:14px;">
    <p class="ok">✅ Concluido — Corrigidas: <?= $totalFix ?> | Erros: <?= $totalErro ?></p>
    <a class="btn gr" href="painel.php">← Voltar ao Painel</a>
</div>
<?php endif; ?>

<?php if (empty($propostas)): ?>
<p class="ok">🎉 Nenhuma proposta com campos vazios! Tudo OK.</p>
<?php else: ?>
<table>
<tr><th>#</th><th>Numero</th><th>Dados</th><th>Status</th></tr>
<?php foreach ($resultados as $r): ?>
<tr>
    <td><?= $r['id'] ?></td>
    <td><?= htmlspecialchars($r['num']) ?></td>
    <td style="font-size:11px;"><?= htmlspecialchars($r['log']) ?></td>
    <td class="<?= str_contains($r['st'],'ATUALIZADO')||str_contains($r['st'],'PREVIEW') ? 'ok' : (str_contains($r['st'],'nada') ? 'info' : 'err') ?>"><?= $r['st'] ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
<p style="color:#475569;margin-top:20px;font-size:11px;">Deletar apos uso.</p>
</body></html>
