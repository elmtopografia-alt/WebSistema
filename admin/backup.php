<?php
/**
 * BACKUP E RESTAURAÇÃO - GEOMETRPOLE
 * 
 * Funcionalidades:
 * - Backup manual completo
 * - Backups automáticos (antes de alterações)
 * - Restauração de temas
 * - Download de backups
 * - Limpeza de backups antigos
 */

require_once '../config/config.php';
require_once '../config/database.php';

session_start();
// Autenticação
require_once 'auth_check.php';

$db = new Database();

// Configurações
define('BACKUP_DIR', '../config/temas/backups/');
define('MAX_BACKUPS', 20); // Manter apenas 20 backups mais recentes

// Ações
$mensagem = '';
$tipoMensagem = '';

// Criar backup manual
if (isset($_POST['criar_backup'])) {
    $nome = $_POST['nome_backup'] ?: 'Backup_' . date('Y-m-d_H-i-s');
    $descricao = $_POST['descricao_backup'] ?: 'Backup manual criado em ' . date('d/m/Y H:i:s');
    
    $arquivo = criarBackup($db, $nome, $descricao, 'manual');
    
    if ($arquivo) {
        $mensagem = "✅ Backup criado: $nome";
        $tipoMensagem = "success";
    } else {
        $mensagem = "❌ Erro ao criar backup";
        $tipoMensagem = "error";
    }
}

// Restaurar backup
if (isset($_GET['restaurar'])) {
    $id = intval($_GET['restaurar']);
    $backup = $db->query("SELECT * FROM temas_backups WHERE id = ?", [$id])->fetch();
    
    if ($backup && file_exists($backup['arquivo'])) {
        $dados = json_decode(file_get_contents($backup['arquivo']), true);
        
        if (restaurarBackup($db, $dados)) {
            $db->query("UPDATE temas_backups SET restaurado_em = NOW() WHERE id = ?", [$id]);
            $mensagem = "✅ Backup restaurado com sucesso!";
            $tipoMensagem = "success";
        } else {
            $mensagem = "❌ Erro ao restaurar backup";
            $tipoMensagem = "error";
        }
    }
}

// Download backup
if (isset($_GET['download'])) {
    $id = intval($_GET['download']);
    $backup = $db->query("SELECT * FROM temas_backups WHERE id = ?", [$id])->fetch();
    
    if ($backup && file_exists($backup['arquivo'])) {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . basename($backup['arquivo']) . '"');
        readfile($backup['arquivo']);
        exit;
    }
}

// Excluir backup
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $backup = $db->query("SELECT * FROM temas_backups WHERE id = ?", [$id])->fetch();
    
    if ($backup) {
        if (file_exists($backup['arquivo'])) {
            unlink($backup['arquivo']);
        }
        $db->query("DELETE FROM temas_backups WHERE id = ?", [$id]);
        $mensagem = "🗑️ Backup excluído";
        $tipoMensagem = "warning";
    }
}

// Limpar backups antigos
if (isset($_GET['limpar'])) {
    $backups = $db->query("SELECT * FROM temas_backups ORDER BY created_at DESC")->fetchAll();
    $total = count($backups);
    
    if ($total > MAX_BACKUPS) {
        $paraExcluir = array_slice($backups, MAX_BACKUPS);
        foreach ($paraExcluir as $backup) {
            if (file_exists($backup['arquivo'])) {
                unlink($backup['arquivo']);
            }
            $db->query("DELETE FROM temas_backups WHERE id = ?", [$backup['id']]);
        }
        $mensagem = "🧹 " . count($paraExcluir) . " backups antigos removidos";
        $tipoMensagem = "success";
    }
}

// Buscar backups
try {
    $backups = $db->query("
        SELECT b.*, 
               COUNT(t.id) as total_temas,
               SUM(CASE WHEN t.is_sistema = 1 THEN 1 ELSE 0 END) as temas_sistema
        FROM temas_backups b
        LEFT JOIN temas_personalizados t ON 1=1
        GROUP BY b.id
        ORDER BY b.created_at DESC
    ")->fetchAll();
} catch (Exception $e) {
    // Handling case where backups table might be empty or query fails
    $backups = [];
}

// Funções
function criarBackup($db, $nome, $descricao, $tipo = 'manual') {
    // Buscar todos os temas
    $temas = $db->query("SELECT * FROM temas_personalizados")->fetchAll();
    
    // Dados do backup
    $dados = [
        'versao' => '2.0.0',
        'data_criacao' => date('Y-m-d H:i:s'),
        'nome' => $nome,
        'descricao' => $descricao,
        'tipo' => $tipo,
        'sistema' => [
            'php_version' => PHP_VERSION,
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown'
        ],
        'temas' => $temas
    ];
    
    // Criar arquivo
    if (!is_dir(BACKUP_DIR)) {
        mkdir(BACKUP_DIR, 0755, true);
    }
    
    $arquivo = BACKUP_DIR . 'backup_' . date('Ymd_His') . '_' . sanitizeFilename($nome) . '.json';
    $json = json_encode($dados, JSON_PRETTY_PRINT);
    
    if (file_put_contents($arquivo, $json)) {
        // Registrar no banco
        $md5 = md5($json);
        $size = strlen($json);
        
        $db->query("INSERT INTO temas_backups 
            (nome, descricao, arquivo, tamanho, checksum, tipo) 
            VALUES 
            (?, ?, ?, ?, ?, ?)", 
            [$nome, $descricao, $arquivo, $size, $md5, $tipo]);
        
        return $arquivo;
    }
    
    return false;
}

function restaurarBackup($db, $dados) {
    if (!isset($dados['temas']) || !is_array($dados['temas'])) {
        return false;
    }
    
    // Backup atual antes de restaurar
    criarBackup($db, 'Pre-restauracao_' . date('Y-m-d_H-i-s'), 'Backup automático antes da restauração', 'pre_atualizacao');
    
    // Limpar temas personalizados atuais (não do sistema)
    $db->query("DELETE FROM temas_personalizados WHERE is_sistema = 0");
    
    // Inserir temas do backup
    foreach ($dados['temas'] as $tema) {
        if (!$tema['is_sistema']) {
             // Handle defaults for missing fields if restoring old backup
             $slug = $tema['slug'] ?? ($tema['nome'] . time());
             $icone = $tema['icone'] ?? 'palette';
             
             $sql = "INSERT INTO temas_personalizados 
                 (nome, slug, descricao, icone, cor_primaria, cor_secundaria, cor_destaque, 
                  cor_sucesso, cor_alerta, fonte_titulo, fonte_corpo, tamanho_base, 
                  espacamento_padrao, bordas_arredondadas, sombras, css_custom, ativo, is_sistema)
                 VALUES 
                 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0)";
                  
             $params = [
                 $tema['nome'], $slug, $tema['descricao'], $icone,
                 $tema['cor_primaria'], $tema['cor_secundaria'], $tema['cor_destaque'],
                 $tema['cor_sucesso'], $tema['cor_alerta'], $tema['fonte_titulo'],
                 $tema['fonte_corpo'], $tema['tamanho_base'], $tema['espacamento_padrao'],
                 $tema['bordas_arredondadas'], $tema['sombras'], $tema['css_custom']
             ];
             
             $db->query($sql, $params);
        }
    }
    
    return true;
}

function sanitizeFilename($nome) {
    return preg_replace('/[^a-zA-Z0-9_-]/', '_', $nome);
}

// Formatar tamanho
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Backup & Restauração - GEOMETRPOLE</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Estilos similares ao temas.php */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            margin: 0;
            color: #333;
        }
        
        .admin-container {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }
        
        .sidebar {
            background: #2c3e50;
            color: white;
            padding: 30px 20px;
            position: fixed;
            height: 100vh;
            width: 280px;
            overflow-y: auto;
        }
        
        .sidebar-logo {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-version {
            font-size: 12px;
            opacity: 0.7;
            margin-bottom: 40px;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
            margin: 5px 0;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .main-content {
            margin-left: 280px;
            padding: 30px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .header h1 {
            color: #2c3e50;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-warning { background: #f39c12; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-secondary { background: #95a5a6; color: white; }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .card h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .backup-list {
            display: grid;
            gap: 15px;
        }
        
        .backup-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }
        
        .backup-item.restaurado {
            border-left-color: #27ae60;
            opacity: 0.7;
        }
        
        .backup-info h4 {
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .backup-meta {
            display: flex;
            gap: 15px;
            font-size: 13px;
            color: #7f8c8d;
        }
        
        .backup-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .backup-actions {
            display: flex;
            gap: 8px;
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-manual { background: #3498db; color: white; }
        .badge-auto { background: #9b59b6; color: white; }
        .badge-pre { background: #e74c3c; color: white; }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-warning { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
        .alert-error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .info-box h4 {
            color: #1976d2;
            margin-bottom: 5px;
        }
        
        .info-box p {
            color: #555;
            font-size: 14px;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                grid-template-columns: 1fr;
            }
            .sidebar {
                display: none;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fas fa-cube"></i> GEOMETRPOLE
            </div>
            <div class="sidebar-version">v2.0.0 - Painel Admin</div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="painel.php" class="nav-link">
                        <i class="fas fa-paint-brush"></i>
                        Personalizar Ativo
                    </a>
                </li>
                <li class="nav-item">
                    <a href="temas.php" class="nav-link">
                        <i class="fas fa-palette"></i>
                        Gerenciar Temas
                    </a>
                </li>
                <li class="nav-item">
                    <a href="backup.php" class="nav-link active">
                        <i class="fas fa-archive"></i>
                        Backup & Restauração
                    </a>
                </li>
            </ul>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-archive"></i> Backup & Restauração</h1>
                <?php if (count($backups) > MAX_BACKUPS): ?>
                    <a href="?limpar=1" class="btn btn-warning" onclick="return confirm('Isso manterá apenas os <?php echo MAX_BACKUPS; ?> backups mais recentes. Continuar?')">
                        <i class="fas fa-broom"></i> Limpar Antigos
                    </a>
                <?php endif; ?>
            </div>
            
            <?php if ($mensagem): ?>
                <div class="alert alert-<?php echo $tipoMensagem; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>
            
            <!-- Info -->
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Sobre os Backups</h4>
                <p>Os backups salvam TODOS os temas (sistema e personalizados). Você pode restaurar um backup completo ou exportar temas individuais na página de gerenciamento.</p>
            </div>
            
            <!-- Criar Backup -->
            <div class="card">
                <h2><i class="fas fa-plus-circle"></i> Criar Novo Backup</h2>
                <form method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Nome do Backup</label>
                            <input type="text" name="nome_backup" class="form-control" placeholder="Ex: Antes da atualização de cores">
                        </div>
                        <div class="form-group">
                            <label>Descrição</label>
                            <input type="text" name="descricao_backup" class="form-control" placeholder="Descreva o motivo do backup">
                        </div>
                    </div>
                    <button type="submit" name="criar_backup" class="btn btn-success">
                        <i class="fas fa-save"></i> Criar Backup Agora
                    </button>
                </form>
            </div>
            
            <!-- Lista de Backups -->
            <div class="card">
                <h2><i class="fas fa-history"></i> Histórico de Backups (<?php echo count($backups); ?>)</h2>
                
                <?php if (empty($backups)): ?>
                    <p style="text-align: center; color: #7f8c8d; padding: 40px;">
                        Nenhum backup encontrado. Crie seu primeiro backup acima!
                    </p>
                <?php else: ?>
                    <div class="backup-list">
                        <?php foreach ($backups as $backup): ?>
                            <div class="backup-item <?php echo $backup['restaurado_em'] ? 'restaurado' : ''; ?>">
                                <div class="backup-info">
                                    <h4>
                                        <?php echo htmlspecialchars($backup['nome']); ?>
                                        <span class="badge badge-<?php echo $backup['tipo']; ?>">
                                            <?php echo $backup['tipo']; ?>
                                        </span>
                                        <?php if ($backup['restaurado_em']): ?>
                                            <span class="badge" style="background: #27ae60;">
                                                <i class="fas fa-check"></i> Restaurado em <?php echo date('d/m/Y', strtotime($backup['restaurado_em'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </h4>
                                    <p style="color: #666; font-size: 13px; margin: 5px 0;">
                                        <?php echo htmlspecialchars($backup['descricao']); ?>
                                    </p>
                                    <div class="backup-meta">
                                        <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($backup['created_at'])); ?></span>
                                        <span><i class="fas fa-database"></i> <?php echo formatBytes($backup['tamanho']); ?></span>
                                        <span><i class="fas fa-fingerprint"></i> <?php echo substr($backup['checksum'], 0, 8); ?>...</span>
                                    </div>
                                </div>
                                <div class="backup-actions">
                                    <a href="?download=<?php echo $backup['id']; ?>" class="btn btn-secondary" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <?php if (!$backup['restaurado_em']): ?>
                                        <a href="?restaurar=<?php echo $backup['id']; ?>" 
                                           class="btn btn-warning" 
                                           title="Restaurar"
                                           onclick="return confirm('ATENÇÃO: Isso substituirá todos os temas atuais pelos do backup. Um backup prévio será criado automaticamente. Continuar?')">
                                            <i class="fas fa-undo"></i> Restaurar
                                        </a>
                                    <?php endif; ?>
                                    <a href="?excluir=<?php echo $backup['id']; ?>" 
                                       class="btn btn-danger" 
                                       title="Excluir"
                                       onclick="return confirm('Excluir permanentemente este backup?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
