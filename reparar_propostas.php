<?php
/**
 * reparar_propostas.php — Acesse com ?token=SGT2026REPAIR
 * Repara campos vazios nas propostas buscando DadosEmpresa e Clientes.
 * DELETE após uso!
 */

// Proteção simples por token (sem depender de sessão)
$TOKEN = 'SGT2026REPAIR';
if (($_GET['token'] ?? '') !== $TOKEN) {
    http_response_code(403);
    die('Acesso negado. Use ?token=SGT2026REPAIR');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$confirmar = isset($_GET['confirmar']) && $_GET['confirmar'] == '1';
$de_id     = (int)($_GET['de'] ?? 1);

// ── Busca todas as propostas com algum campo vazio ─────────────────────────
$sql = "SELECT p.id_proposta, p.numero_proposta, p.id_cliente, p.id_criador,
               p.empresa_proponente_nome, p.empresa_proponente_cnpj, p.empresa_proponente_banco,
               p.nome_cliente_salvo, p.email_salvo
        FROM Propostas p
        WHERE p.id_proposta >= $de_id
          AND (
              (p.empresa_proponente_nome IS NULL OR p.empresa_proponente_nome = '')
              OR (p.nome_cliente_salvo IS NULL OR p.nome_cliente_salvo = '')
              OR (p.email_salvo IS NULL OR p.email_salvo = '')
          )
        ORDER BY p.id_proposta ASC";

$propostas = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Cache de empresas e clientes para evitar consultas repetidas
$cacheEmpresa = [];
$cacheCliente = [];

$resultados  = [];
$totalFix    = 0;
$totalErro   = 0;

foreach ($propostas as $p) {
    $id        = $p['id_proposta'];
    $idCriador = (int)$p['id_criador'];
    $idCliente = (int)$p['id_cliente'];

    $campos = [];
    $log    = [];

    // ── Empresa Proponente ────────────────────────────────────────────
    if (empty($p['empresa_proponente_nome'])) {
        if (!isset($cacheEmpresa[$idCriador])) {
            $cacheEmpresa[$idCriador] = $conn->query(
                "SELECT * FROM DadosEmpresa WHERE id_criador = $idCriador LIMIT 1"
            )->fetch_assoc();
        }
        $emp = $cacheEmpresa[$idCriador];
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
            $log[] = "✅ Empresa: " . ($emp['Empresa'] ?? 'N/A');
        } else {
            $log[] = "❌ DadosEmpresa não encontrada para criador #$idCriador";
        }
    }

    // ── Dados do Cliente ─────────────────────────────────────────────
    if (empty($p['nome_cliente_salvo']) || empty($p['email_salvo'])) {
        if ($idCliente > 0) {
            if (!isset($cacheCliente[$idCliente])) {
                $cacheCliente[$idCliente] = $conn->query(
                    "SELECT nome_cliente, empresa, email, telefone, celular FROM Clientes WHERE id_cliente = $idCliente"
                )->fetch_assoc();
            }
            $cli = $cacheCliente[$idCliente];
            if ($cli) {
                if (empty($p['nome_cliente_salvo']))
                    $campos['nome_cliente_salvo']  = $conn->real_escape_string($cli['nome_cliente'] ?? '');
                if (empty($p['email_salvo']))
                    $campos['email_salvo']         = $conn->real_escape_string($cli['email'] ?? '');
                if (empty($p['telefone_salvo'] ?? ''))
                    $campos['telefone_salvo']      = $conn->real_escape_string($cli['telefone'] ?? '');
                $campos['empresa_cliente_salvo']   = $conn->real_escape_string($cli['empresa'] ?? '');
                $campos['celular_salvo']           = $conn->real_escape_string($cli['celular'] ?? '');
                $campos['whatsapp_salvo']          = $conn->real_escape_string($cli['celular'] ?? '');
                $log[] = "✅ Cliente: " . ($cli['nome_cliente'] ?? 'N/A');
            } else {
                $log[] = "❌ Cliente #$idCliente não encontrado";
            }
        } else {
            $log[] = "⚠️ id_cliente = 0 (proposta sem cliente vinculado)";
        }
    }

    // ── Executa UPDATE se em modo confirmar ───────────────────────────
    $status = 'sem alteração';
    if (!empty($campos)) {
        $set = implode(', ', array_map(fn($k, $v) => "$k = '$v'", array_keys($campos), $campos));
        $sqlUpd = "UPDATE Propostas SET $set WHERE id_proposta = $id";

        if ($confirmar) {
            if ($conn->query($sqlUpd)) {
                $status = '✅ ATUALIZADO';
                $totalFix++;
            } else {
                $status = '❌ ERRO: ' . $conn->error;
                $totalErro++;
            }
        } else {
            $status = '🔍 DRY-RUN (não salvo)';
        }
    }

    $resultados[] = [
        'id'     => $id,
        'numero' => $p['numero_proposta'],
        'campos' => count($campos),
        'log'    => implode(' | ', $log),
        'status' => $status,
    ];
}

// ── HTML ─────────────────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Reparar Propostas</title>
<style>
body{font-family:monospace;background:#0f172a;color:#e2e8f0;padding:20px;font-size:13px;}
h2{color:#38bdf8;} h3{color:#fbbf24;}
.btn{display:inline-block;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:bold;margin:8px 4px;}
.btn-blue{background:#2563eb;color:#fff;} .btn-green{background:#16a34a;color:#fff;}
.btn-gray{background:#334155;color:#e2e8f0;}
table{border-collapse:collapse;width:100%;margin-top:16px;}
th{background:#1e293b;color:#94a3b8;padding:8px 12px;text-align:left;}
td{padding:6px 12px;border-bottom:1px solid #1e293b;}
.ok{color:#10b981;} .err{color:#ef4444;} .warn{color:#f97316;} .dry{color:#a78bfa;}
.badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:11px;}
</style></head><body>
<h2>🔧 Reparo em Massa de Propostas</h2>

<?php if (!$confirmar): ?>
<div style="background:#1e293b;border:1px solid #f97316;border-radius:8px;padding:16px;margin-bottom:16px;">
    <p class="warn">⚠️ <strong>Modo DRY-RUN</strong> — Nenhuma alteração será feita. Veja o preview abaixo, depois confirme.</p>
    <a class="btn btn-green" href="?confirmar=1&de=<?= $de_id ?>">
        ✅ Confirmar e Aplicar Reparos
    </a>
    <a class="btn btn-gray" href="painel.php">Cancelar</a>
</div>
<?php else: ?>
<div style="background:#1e293b;border:1px solid #10b981;border-radius:8px;padding:16px;margin-bottom:16px;">
    <p class="ok">✅ <strong>Modo ATIVO</strong> — Alterações gravadas no banco.</p>
    <span class="ok">Corrigidas: <?= $totalFix ?></span> &nbsp;|&nbsp;
    <span class="err">Erros: <?= $totalErro ?></span>
    <br><br>
    <a class="btn btn-gray" href="painel.php">← Voltar ao Painel</a>
</div>
<?php endif; ?>

<p>Propostas com campos vazios a partir do ID <strong>#<?= $de_id ?></strong>: 
   <strong><?= count($propostas) ?></strong> encontradas</p>

<?php if (empty($propostas)): ?>
    <p class="ok">🎉 Nenhuma proposta com campos críticos vazios! Tudo OK.</p>
<?php else: ?>
<table>
    <tr><th>#ID</th><th>Número</th><th>Campos a Reparar</th><th>Dados Encontrados</th><th>Status</th></tr>
    <?php foreach ($resultados as $r): ?>
    <tr>
        <td><?= $r['id'] ?></td>
        <td><?= htmlspecialchars($r['numero']) ?></td>
        <td><span class="badge" style="background:<?= $r['campos'] > 0 ? '#92400e' : '#1e3a2f' ?>;color:<?= $r['campos'] > 0 ? '#fbbf24' : '#10b981' ?>"><?= $r['campos'] ?> campos</span></td>
        <td style="font-size:11px;"><?= htmlspecialchars($r['log']) ?></td>
        <td class="<?= str_contains($r['status'],'ATUALIZADO') ? 'ok' : (str_contains($r['status'],'DRY') ? 'dry' : (str_contains($r['status'],'sem') ? '' : 'err')) ?>"><?= $r['status'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<p style="color:#475569;margin-top:24px;font-size:11px;">⚠️ Delete após uso: /Orcamento/reparar_propostas.php</p>
</body></html>
