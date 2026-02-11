<?php
/**
 * GERENCIADOR DE TEMAS - GEOMETRPOLE
 * 
 * Funcionalidades:
 * - Listar todos os temas
 * - Ativar/Desativar temas
 * - Duplicar temas
 * - Excluir temas personalizados
 * - Preview rápido
 * - Exportar/Importar
 */

require_once '../config/config.php';
require_once '../config/database.php';

// Autenticação
require_once 'auth_check.php';

$db = new Database();

// Ações
$mensagem = '';
$tipoMensagem = '';

// Ativar tema
if (isset($_GET['ativar'])) {
    $id = intval($_GET['ativar']);
    
    // Desativar todos
    $db->query("UPDATE temas_personalizados SET ativo = 0");
    
    // Ativar selecionado
    $db->query("UPDATE temas_personalizados SET ativo = 1 WHERE id = ?", [$id]);
    
    // Registrar no histórico
    $usuario = $_SESSION['admin_nome'] ?? 'admin';
    $db->query("INSERT INTO temas_historico (tema_id, acao, usuario) VALUES (?, 'ativar', ?)", [$id, $usuario]);
    
    // Gerar CSS do tema ativo
    $temaAtivo = $db->query("SELECT * FROM temas_personalizados WHERE id = ?", [$id])->fetch();
    if ($temaAtivo) {
        gerarCSSAtivo($temaAtivo);
    }
    
    $mensagem = "✅ Tema ativado com sucesso!";
    $tipoMensagem = "success";
}

// Duplicar tema
if (isset($_GET['duplicar'])) {
    $id = intval($_GET['duplicar']);
    $tema = $db->query("SELECT * FROM temas_personalizados WHERE id = ?", [$id])->fetch();
    
    if ($tema) {
        $novoNome = $tema['nome'] . ' (Cópia)';
        $novoSlug = $tema['slug'] . '_copia_' . time();
        
        // Handle potentially missing fields with defaults
        $descricao = $tema['descricao'] ?? '';
        $icone = $tema['icone'] ?? 'palette';
        $cor_primaria = $tema['cor_primaria'] ?? '#2c3e50';
        $cor_secundaria = $tema['cor_secundaria'] ?? '#34495e';
        $cor_destaque = $tema['cor_destaque'] ?? '#3498db';
        $cor_sucesso = $tema['cor_sucesso'] ?? '#27ae60';
        $cor_alerta = $tema['cor_alerta'] ?? '#ffc107';
        $fonte_titulo = $tema['fonte_titulo'] ?? 'Segoe UI';
        $fonte_corpo = $tema['fonte_corpo'] ?? 'Segoe UI';
        $tamanho_base = $tema['tamanho_base'] ?? '11pt';
        $espacamento_padrao = $tema['espacamento_padrao'] ?? '15px';
        $bordas_arredondadas = $tema['bordas_arredondadas'] ?? 1;
        $sombras = $tema['sombras'] ?? 1;
        $css_custom = $tema['css_custom'] ?? '';
        
        $sql = "INSERT INTO temas_personalizados 
            (nome, slug, descricao, icone, cor_primaria, cor_secundaria, cor_destaque, 
             cor_sucesso, cor_alerta, fonte_titulo, fonte_corpo, tamanho_base, 
             espacamento_padrao, bordas_arredondadas, sombras, css_custom, ativo)
            VALUES 
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";
        
        $params = [
            $novoNome, $novoSlug, $descricao, $icone,
            $cor_primaria, $cor_secundaria, $cor_destaque,
            $cor_sucesso, $cor_alerta, $fonte_titulo,
            $fonte_corpo, $tamanho_base, $espacamento_padrao,
            $bordas_arredondadas, $sombras, $css_custom
        ];
        
        $db->query($sql, $params);
        $novoId = $db->lastInsertId();
        
        // Registrar histórico
        $usuario = $_SESSION['admin_nome'] ?? 'admin';
        $dadosNovos = json_encode(['original_id' => $id]);
        $db->query("INSERT INTO temas_historico (tema_id, acao, dados_novos, usuario) 
            VALUES (?, 'duplicar', ?, ?)", [$novoId, $dadosNovos, $usuario]);
        
        $mensagem = "✅ Tema duplicado! Edite a cópia agora.";
        $tipoMensagem = "success";
    }
}

// Excluir tema (apenas personalizados, não do sistema)
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $tema = $db->query("SELECT * FROM temas_personalizados WHERE id = ?", [$id])->fetch();
    
    if ($tema && !$tema['is_sistema']) {
        // Backup antes de excluir
        if (!is_dir('../config/temas/backups/')) {
            mkdir('../config/temas/backups/', 0755, true);
        }
        $backupArquivo = '../config/temas/backups/excluido_' . $tema['slug'] . '_' . date('Ymd_His') . '.json';
        file_put_contents($backupArquivo, json_encode($tema, JSON_PRETTY_PRINT));
        
        $db->query("DELETE FROM temas_personalizados WHERE id = ?", [$id]);
        $usuario = $_SESSION['admin_nome'] ?? 'admin';
        $dadosAnteriores = json_encode($tema);
        $db->query("INSERT INTO temas_historico (tema_id, acao, dados_anteriores, usuario) 
            VALUES (?, 'excluir', ?, ?)", [$id, $dadosAnteriores, $usuario]);
        
        $mensagem = "🗑️ Tema excluído. Backup salvo em: $backupArquivo";
        $tipoMensagem = "warning";
    }
}

// Buscar todos os temas
$temas = $db->query("SELECT * FROM temas_personalizados ORDER BY is_sistema DESC, nome ASC")->fetchAll();

// Estatísticas
$totalTemas = count($temas);
$temasSistema = count(array_filter($temas, fn($t) => $t['is_sistema']));
$temasPersonalizados = $totalTemas - $temasSistema;

function gerarCSSAtivo($tema) {
    $css = "/**
 * TEMA PERSONALIZADO - GEOMETRPOLE
 * Gerado automaticamente em " . date('d/m/Y H:i:s') . "
 */

:root {
    /* Cores da Marca */
    --cor-primaria: {$tema['cor_primaria']};
    --cor-secundaria: {$tema['cor_secundaria']};
    --cor-destaque: {$tema['cor_destaque']};
    --cor-sucesso: " . ($tema['cor_sucesso'] ?? '#27ae60') . ";
    --cor-alerta: " . ($tema['cor_alerta'] ?? '#ffc107') . ";
    
    /* Fontes */
    --fonte-titulo: '" . ($tema['fonte_titulo'] ?? 'Segoe UI') . "', sans-serif;
    --fonte-corpo: '" . ($tema['fonte_corpo'] ?? 'Segoe UI') . "', sans-serif;
    
    /* Tamanhos */
    --tamanho-base: " . ($tema['tamanho_base'] ?? '11pt') . ";
    
    /* Espaçamentos */
    --espacamento-padrao: " . ($tema['espacamento_padrao'] ?? '15px') . ";
    
    /* Bordas */
    --raio-borda: " . (($tema['bordas_arredondadas'] ?? 1) ? '8px' : '0px') . ";
    
    /* Sombras */
    --sombra-padrao: " . (($tema['sombras'] ?? 1) ? '0 4px 6px rgba(0,0,0,0.1)' : 'none') . ";
}

/* Aplicar fontes */
h1, h2, h3, h4, h5, h6 {
    font-family: var(--fonte-titulo);
}

body {
    font-family: var(--fonte-corpo);
}

/* Aplicar cores */
.section-title {
    background: linear-gradient(135deg, var(--cor-primaria) 0%, var(--cor-secundaria) 100%);
}

.service-highlight {
    border-left-color: var(--cor-destaque);
}

.price-box {
    border-color: var(--cor-primaria);
}

.price-value {
    color: var(--cor-primaria);
}

/* CSS Customizado do usuário */
" . ($tema['css_custom'] ?? '') . "
";
    
    file_put_contents('../templates/css/tema-dinamico.css', $css);
}

// Ícones disponíveis
$icones = [
    'palette' => '🎨',
    'moon' => '🌙',
    'minimize' => '⊟',
    'leaf' => '🍃',
    'cpu' => '💻',
    'sun' => '☀️',
    'star' => '⭐',
    'heart' => '❤️',
    'zap' => '⚡',
    'cloud' => '☁️'
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Temas - GEOMETRPOLE</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        
        .admin-container {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }
        
        /* Sidebar */
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
        }
        
        .nav-item {
            margin: 5px 0;
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
        }
        
        .nav-link:hover,
        .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        /* Main Content */
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
            font-size: 28px;
            color: #2c3e50;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
        }
        
        .btn-warning {
            background: #f39c12;
            color: white;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .stat-icon.blue { background: #e3f2fd; color: #1976d2; }
        .stat-icon.green { background: #e8f5e9; color: #388e3c; }
        .stat-icon.orange { background: #fff3e0; color: #f57c00; }
        
        .stat-info h3 {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-info p {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        /* Temas Grid */
        .temas-section {
            margin-bottom: 40px;
        }
        
        .section-title {
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .temas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .tema-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s;
            position: relative;
        }
        
        .tema-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .tema-card.active {
            border: 3px solid #27ae60;
        }
        
        .tema-card.sistema {
            border-left: 4px solid #3498db;
        }
        
        .tema-preview {
            height: 150px;
            position: relative;
            overflow: hidden;
        }
        
        .tema-preview-bg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .tema-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .tema-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-sistema {
            background: #3498db;
            color: white;
        }
        
        .badge-personalizado {
            background: #9b59b6;
            color: white;
        }
        
        .badge-ativo {
            background: #27ae60;
            color: white;
            left: 10px;
            right: auto;
        }
        
        .tema-info {
            padding: 20px;
        }
        
        .tema-info h3 {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .tema-info p {
            color: #7f8c8d;
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        
        .tema-cores {
            display: flex;
            gap: 8px;
            margin-bottom: 15px;
        }
        
        .cor-preview {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 0 0 1px #ddd;
        }
        
        .tema-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .btn-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 20px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        /* Alertas */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        /* Responsivo */
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
            
            .temas-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
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
                    <a href="temas.php" class="nav-link active">
                        <i class="fas fa-palette"></i>
                        Gerenciar Temas
                    </a>
                </li>
                <li class="nav-item">
                    <a href="backup.php" class="nav-link">
                        <i class="fas fa-archive"></i>
                        Backup & Restauração
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-cog"></i>
                        Configurações
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-palette"></i> Gerenciar Temas</h1>
                <div class="header-actions">
                    <a href="importar.php" class="btn btn-secondary">
                        <i class="fas fa-upload"></i> Importar
                    </a>
                    <a href="painel.php?novo=1" class="btn btn-success">
                        <i class="fas fa-plus"></i> Criar Novo Tema
                    </a>
                </div>
            </div>
            
            <?php if ($mensagem): ?>
                <div class="alert alert-<?php echo $tipoMensagem; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>
            
            <!-- Estatísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalTemas; ?></h3>
                        <p>Total de Temas</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $temasSistema; ?></h3>
                        <p>Temas do Sistema</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $temasPersonalizados; ?></h3>
                        <p>Seus Temas</p>
                    </div>
                </div>
            </div>
            
            <!-- Temas do Sistema -->
            <div class="temas-section">
                <h2 class="section-title">
                    <i class="fas fa-star"></i> Temas do Sistema
                </h2>
                <div class="temas-grid">
                    <?php foreach (array_filter($temas, fn($t) => $t['is_sistema']) as $tema): ?>
                        <div class="tema-card sistema <?php echo $tema['ativo'] ? 'active' : ''; ?>">
                            <div class="tema-preview" style="background: linear-gradient(135deg, <?php echo $tema['cor_primaria']; ?> 0%, <?php echo $tema['cor_secundaria']; ?> 100%);">
                                <?php if ($tema['ativo']): ?>
                                    <span class="tema-badge badge-ativo">
                                        <i class="fas fa-check"></i> Ativo
                                    </span>
                                <?php endif; ?>
                                <span class="tema-badge badge-sistema">Sistema</span>
                                <div class="tema-preview-bg">
                                    <div class="tema-icon"><?php echo $icones[$tema['icone']] ?? '🎨'; ?></div>
                                    <small>Preview</small>
                                </div>
                            </div>
                            <div class="tema-info">
                                <h3><?php echo htmlspecialchars($tema['nome']); ?></h3>
                                <p><?php echo htmlspecialchars($tema['descricao']); ?></p>
                                <div class="tema-cores">
                                    <div class="cor-preview" style="background: <?php echo $tema['cor_primaria']; ?>" title="Primária"></div>
                                    <div class="cor-preview" style="background: <?php echo $tema['cor_secundaria']; ?>" title="Secundária"></div>
                                    <div class="cor-preview" style="background: <?php echo $tema['cor_destaque']; ?>" title="Destaque"></div>
                                </div>
                                <div class="tema-actions">
                                    <?php if (!$tema['ativo']): ?>
                                        <a href="?ativar=<?php echo $tema['id']; ?>" class="btn btn-success btn-small">
                                            <i class="fas fa-check"></i> Ativar
                                        </a>
                                    <?php endif; ?>
                                    <a href="painel.php?editar=<?php echo $tema['id']; ?>" class="btn btn-primary btn-small">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="exportar.php?id=<?php echo $tema['id']; ?>" class="btn btn-secondary btn-small" title="Exportar">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="?duplicar=<?php echo $tema['id']; ?>" class="btn btn-warning btn-small" title="Duplicar">
                                        <i class="fas fa-copy"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Temas Personalizados -->
            <div class="temas-section">
                <h2 class="section-title">
                    <i class="fas fa-user"></i> Seus Temas Personalizados
                </h2>
                
                <?php if ($temasPersonalizados === 0): ?>
                    <div class="empty-state">
                        <i class="fas fa-palette"></i>
                        <h3>Nenhum tema personalizado</h3>
                        <p>Duplique um tema do sistema ou crie um novo para começar!</p>
                    </div>
                <?php else: ?>
                    <div class="temas-grid">
                        <?php foreach (array_filter($temas, fn($t) => !$t['is_sistema']) as $tema): ?>
                            <div class="tema-card <?php echo $tema['ativo'] ? 'active' : ''; ?>">
                                <div class="tema-preview" style="background: linear-gradient(135deg, <?php echo $tema['cor_primaria']; ?> 0%, <?php echo $tema['cor_secundaria']; ?> 100%);">
                                    <?php if ($tema['ativo']): ?>
                                        <span class="tema-badge badge-ativo">
                                            <i class="fas fa-check"></i> Ativo
                                        </span>
                                    <?php endif; ?>
                                    <span class="tema-badge badge-personalizado">Personalizado</span>
                                    <div class="tema-preview-bg">
                                        <div class="tema-icon"><?php echo $icones[$tema['icone']] ?? '🎨'; ?></div>
                                        <small>Preview</small>
                                    </div>
                                </div>
                                <div class="tema-info">
                                    <h3><?php echo htmlspecialchars($tema['nome']); ?></h3>
                                    <p><?php echo htmlspecialchars($tema['descricao']); ?></p>
                                    <div class="tema-cores">
                                        <div class="cor-preview" style="background: <?php echo $tema['cor_primaria']; ?>" title="Primária"></div>
                                        <div class="cor-preview" style="background: <?php echo $tema['cor_secundaria']; ?>" title="Secundária"></div>
                                        <div class="cor-preview" style="background: <?php echo $tema['cor_destaque']; ?>" title="Destaque"></div>
                                    </div>
                                    <div class="tema-actions">
                                        <?php if (!$tema['ativo']): ?>
                                            <a href="?ativar=<?php echo $tema['id']; ?>" class="btn btn-success btn-small">
                                                <i class="fas fa-check"></i> Ativar
                                            </a>
                                        <?php endif; ?>
                                        <a href="painel.php?editar=<?php echo $tema['id']; ?>" class="btn btn-primary btn-small">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <a href="exportar.php?id=<?php echo $tema['id']; ?>" class="btn btn-secondary btn-small" title="Exportar">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="?duplicar=<?php echo $tema['id']; ?>" class="btn btn-warning btn-small" title="Duplicar">
                                            <i class="fas fa-copy"></i>
                                        </a>
                                        <a href="?excluir=<?php echo $tema['id']; ?>" 
                                           class="btn btn-danger btn-small" 
                                           title="Excluir"
                                           onclick="return confirm('Tem certeza? O tema será excluído permanentemente (com backup).')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
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
