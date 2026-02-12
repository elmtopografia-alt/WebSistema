<?php
/**
 * SGT PROPOSTAS - MODO SEGURO (Central de Controle de Emergência)
 * 
 * Acesso: https://seusistema.com.br/modo_seguro.php
 * Autorização: Chave diária baseada em algoritmo.
 * 
 * FUNÇÃO: Status em tempo real + Controle de wrappers + Rollback manual
 * TUDO É LOGADO — não há ações invisíveis.
 */

// =============================================================================
// CONFIGURAÇÃO DE SEGURANÇA
// =============================================================================

// Senha mestra para o hash do dia (Mantenha em segredo)
define('SGT_EMERGENCY_PASS', 'SGT2025Emerg');

// Caminho dos arquivos críticos para monitoramento de wrappers
$arquivosMonitorados = [
    'painel_crm.php' => [
        'wrapper' => 'painel_crm.WRAPPER.php',
        'legado' => 'painel_crm.LEGADO.php',
        'desc' => 'Painel CRM - Controle de Clientes'
    ],
    'gerar_proposta_html.php' => [
        'wrapper' => 'gerar_proposta_html.WRAPPER.php',
        'legado' => 'gerar_proposta_html.LEGADO.php',
        'desc' => 'Gerador de Propostas HTML'
    ],
    'gerar_documento.php' => [
        'wrapper' => 'gerar_documento.WRAPPER.php',
        'legado' => 'gerar_documento.LEGADO.php',
        'desc' => 'Gerador de Documentos Word'
    ],
    'editor_avancado.php' => [
        'wrapper' => 'editor_avancado.php', // O próprio arquivo é o wrapper
        'legado' => 'editor_dinamico.php', // No caso do editor, o "legado" é o arquivo direto
        'desc' => 'Wrapper do Editor Avançado'
    ]
];

// Diretório de logs
$pastaLogs = __DIR__ . '/logs/';
if (!is_dir($pastaLogs)) mkdir($pastaLogs, 0755, true);
$arquivoLog = $pastaLogs . 'modo_seguro_' . date('Y-m-d') . '.log';

// =============================================================================
// FUNÇÕES DE SEGURANÇA
// =============================================================================

function verificarChave($chaveInput) {
    // Gera hash do dia: senha_secreta + data_atual
    $chaveEsperada = hash('sha256', SGT_EMERGENCY_PASS . date('Y-m-d'));
    // Aceita também os primeiros 16 caracteres para facilitar o uso manual se necessário
    $chaveCurta = substr($chaveEsperada, 0, 16);
    return hash_equals($chaveEsperada, $chaveInput) || hash_equals($chaveCurta, $chaveInput);
}

function registrarAcao($acao, $usuario = 'SISTEMA', $detalhes = []) {
    global $arquivoLog;
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
    $entrada = [
        'timestamp' => $timestamp,
        'ip' => $ip,
        'usuario' => $usuario,
        'acao' => $acao,
        'detalhes' => $detalhes
    ];
    file_put_contents($arquivoLog, json_encode($entrada) . "\n", FILE_APPEND | LOCK_EX);
}

function verificarEstadoArquivo($arquivo) {
    if (!file_exists($arquivo)) return ['status' => 'AUSENTE', 'cor' => '🔴'];
    $conteudo = @file_get_contents($arquivo);
    if (strpos((string)$conteudo, 'WRAPPER') !== false) return ['status' => 'WRAPPER_ATIVO', 'cor' => '🟡'];
    if (strpos((string)$conteudo, 'require_once') !== false && strpos((string)$conteudo, '.php') !== false) return ['status' => 'ESTRUTURA_DIFERENTE', 'cor' => '🔵'];
    return ['status' => 'LEGADO/PURO', 'cor' => '⚪'];
}

// =============================================================================
// CONTROLE DE ACESSO
// =============================================================================

session_start();
$autenticado = $_SESSION['modo_seguro_auth'] ?? false;
$erro = '';

if (isset($_GET['logout'])) {
    unset($_SESSION['modo_seguro_auth']);
    header('Location: modo_seguro.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['chave_acesso'])) {
    $chave = $_POST['chave_acesso'] ?? '';
    if (verificarChave($chave)) {
        $_SESSION['modo_seguro_auth'] = true;
        $autenticado = true;
        registrarAcao('ACESSO_AUTORIZADO', 'ADMIN');
    } else {
        $erro = 'Chave inválida para ' . date('d/m/Y') . '. Verifique o algoritmo.';
        registrarAcao('TENTATIVA_ACESSO_NEGADA', 'DESCONHECIDO', ['chave_tentada' => substr($chave, 0, 4) . '...']);
    }
}

// =============================================================================
// AÇÕES DE CONTROLE
// =============================================================================

$mensagem = '';
$tipoMensagem = '';

if ($autenticado && isset($_POST['acao'])) {
    $alvo = $_POST['arquivo'] ?? '';
    
    switch ($_POST['acao']) {
        case 'desativar_wrapper':
            if (isset($arquivosMonitorados[$alvo])) {
                $config = $arquivosMonitorados[$alvo];
                if (file_exists($config['legado'])) {
                    // Backup por segurança antes de sobrescrever
                    if (file_exists($alvo)) copy($alvo, $alvo . '.bak_' . date('Ymd_His'));
                    // Sobrescreve com o legado
                    if (copy($config['legado'], $alvo)) {
                        registrarAcao('WRAPPER_DESATIVADO', 'ADMIN', ['arquivo' => $alvo]);
                        $mensagem = "✅ Wrapper de <b>{$alvo}</b> desativado. Modo LEGADO restaurado.";
                        $tipoMensagem = 'sucesso';
                    }
                } else {
                    $mensagem = "❌ Arquivo legado (Backup) não encontrado para {$alvo}.";
                    $tipoMensagem = 'erro';
                }
            }
            break;
            
        case 'reativar_wrapper':
            if (isset($arquivosMonitorados[$alvo])) {
                $config = $arquivosMonitorados[$alvo];
                if (file_exists($config['wrapper'])) {
                    if (copy($config['wrapper'], $alvo)) {
                        registrarAcao('WRAPPER_REATIVADO', 'ADMIN', ['arquivo' => $alvo]);
                        $mensagem = "✅ Wrapper de <b>{$alvo}</b> reativado com sucesso.";
                        $tipoMensagem = 'sucesso';
                    }
                } else {
                    $mensagem = "❌ Arquivo do Wrapper original não encontrado.";
                    $tipoMensagem = 'erro';
                }
            }
            break;
            
        case 'rollback_total':
            $count = 0;
            foreach ($arquivosMonitorados as $nome => $config) {
                if (file_exists($config['legado'])) {
                    if (file_exists($nome)) copy($nome, $nome . '.panic_bak_' . date('Ymd_His'));
                    copy($config['legado'], $nome);
                    $count++;
                }
            }
            registrarAcao('ROLLBACK_TOTAL_EXECUTADO', 'ADMIN', ['arquivos_afetados' => $count]);
            $mensagem = "🚨 ROLLBACK TOTAL EXECUTADO. {$count} arquivos restaurados para versão legada.";
            $tipoMensagem = 'alerta';
            break;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT - Modo Seguro</title>
    <style>
        :root { --danger: #ef4444; --warning: #f59e0b; --success: #10b981; --info: #3b82f6; --bg: #0f172a; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: #f8fafc; line-height: 1.5; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: #1e293b; border-radius: 16px; border: 1px solid rgba(255,255,255,0.1); overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); }
        .header { background: var(--danger); padding: 30px; text-align: center; border-bottom: 4px solid rgba(0,0,0,0.2); }
        .header h1 { font-size: 24px; text-transform: uppercase; letter-spacing: 2px; }
        .content { padding: 40px; }
        .card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 20px; margin-bottom: 20px; transition: 0.3s; }
        .card:hover { border-color: rgba(255,255,255,0.2); }
        .btn { border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 13px; text-transform: uppercase; transition: 0.2s; }
        .btn-primary { background: var(--success); color: white; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-full { width: 100%; display: block; }
        .input { width: 100%; padding: 15px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; font-size: 18px; text-align: center; margin-bottom: 20px; }
        .status-badge { float: right; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 800; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; text-align: center; }
        .alert-sucesso { background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid #10b981; }
        .alert-erro { background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid #ef4444; }
        .alert-alerta { background: rgba(245, 158, 11, 0.2); color: #f59e0b; border: 1px solid #f59e0b; }
        .log-box { background: #000; padding: 15px; border-radius: 8px; font-family: 'Courier New', monospace; font-size: 12px; color: #10b981; max-height: 200px; overflow-y: auto; border: 1px solid #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td { padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .footer { text-align: center; margin-top: 30px; font-size: 11px; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛡️ modo_seguro.php</h1>
            <p>Protocolo de Emergência - SGT Propostas</p>
        </div>
        
        <div class="content">
            <?php if (!$autenticado): ?>
                <div style="max-width: 400px; margin: 0 auto; text-align: center;">
                    <h2 style="margin-bottom: 20px;">Acesso Restrito</h2>
                    <p style="color: #94a3b8; margin-bottom: 30px; font-size: 14px;">Insira a chave de autorização do dia para liberar o painel de emergência.</p>
                    
                    <?php if ($erro): ?><div class="alert alert-erro"><?php echo $erro; ?></div><?php endif; ?>
                    
                    <form method="POST">
                        <input type="password" name="chave_acesso" class="input" placeholder="••••••••••••••••" autofocus required>
                        <button type="submit" class="btn btn-primary btn-full">Autenticar Sistema</button>
                    </form>
                    <div style="margin-top: 20px; font-size: 12px; color: #64748b;">
                        Dica: O hash começa com <code><?php echo substr(hash('sha256', SGT_EMERGENCY_PASS . date('Y-m-d')), 0, 4); ?>...</code>
                    </div>
                </div>
            <?php else: ?>
                <?php if ($mensagem): ?>
                    <div class="alert alert-<?php echo $tipoMensagem; ?>"><?php echo $mensagem; ?></div>
                <?php endif; ?>

                <div class="card">
                    <h3 style="margin-bottom: 15px;">🔍 Verificação de Integridade</h3>
                    <table>
                        <?php foreach($arquivosMonitorados as $nome => $conf): 
                            $estado = verificarEstadoArquivo($nome);
                        ?>
                        <tr>
                            <td>
                                <b><?php echo $nome; ?></b><br>
                                <small style="color: #64748b;"><?php echo $conf['desc']; ?></small>
                            </td>
                            <td>
                                <span class="status-badge" style="background: rgba(255,255,255,0.1); color: <?php echo ($estado['cor'] == '🔴') ? '#ef4444' : (($estado['cor'] == '🟢') ? '#10b981' : '#f59e0b'); ?>;">
                                    <?php echo $estado['cor'] . ' ' . $estado['status']; ?>
                                </span>
                            </td>
                            <td style="text-align: right; width: 140px;">
                                <?php if($estado['status'] === 'WRAPPER_ATIVO'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="arquivo" value="<?php echo $nome; ?>">
                                        <input type="hidden" name="acao" value="desativar_wrapper">
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 10px; font-size: 10px;">Desativar</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST">
                                        <input type="hidden" name="arquivo" value="<?php echo $nome; ?>">
                                        <input type="hidden" name="acao" value="reativar_wrapper">
                                        <button type="submit" class="btn btn-primary" style="padding: 6px 10px; font-size: 10px;">Reativar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>

                <div class="card" style="border-color: var(--danger);">
                    <h3 style="color: var(--danger); margin-bottom: 15px;">🚨 Botão de Pânico (Rollback Total)</h3>
                    <p style="font-size: 13px; color: #94a3b8; margin-bottom: 15px;">Esta ação tentará restaurar a versão LEGADA de todos os arquivos monitorados simultaneamente. Use apenas se o sistema principal estiver 100% inoperante.</p>
                    <form method="POST">
                        <input type="hidden" name="acao" value="rollback_total">
                        <button type="submit" class="btn btn-danger btn-full" onclick="return confirm('Deseja executar o Rollback Total agora?')">Executar Rollback de Emergência</button>
                    </form>
                </div>

                <div class="card">
                    <h3 style="margin-bottom: 15px;">📝 Registro de Atividades (Auditoria)</h3>
                    <div class="log-box">
                        <?php 
                        if (file_exists($arquivoLog)) {
                            $linhas = array_reverse(file($arquivoLog));
                            foreach(array_slice($linhas, 0, 10) as $l) {
                                $d = json_decode($l, true);
                                echo "[{$d['timestamp']}] <b>{$d['acao']}</b> ({$d['ip']})<br>";
                            }
                        } else {
                            echo "Nenhum log registrado para hoje.";
                        }
                        ?>
                    </div>
                </div>

                <div style="text-align: center;">
                    <a href="?logout=1" style="color: #64748b; font-size: 12px; text-decoration: none;">Encerrar Sessão de Emergência</a>
                </div>
            <?php endif; ?>
        </div>
        <div class="footer">
            SGT Ethics Bible Protocol • Fase 5 Segurança • <?php echo date('Y'); ?>
        </div>
    </div>
</body>
</html>
