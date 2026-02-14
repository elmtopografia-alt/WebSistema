<?php
/**
 * PAINEL DE PERSONALIZAÇÃO - GEOMETRPOLE
 * 
 * Permite customizar:
 * - Cores da marca
 * - Fontes
 * - Logo
 * - Layout (espaçamentos, bordas)
 * - CSS customizado
 */

require_once '../config/config.php';
require_once '../config/database.php';

session_start();

// Autenticação Obrigatória
require_once 'auth_check.php';

// Buscar tema atual
$db = new Database();
// Check if table exists before querying to avoid fatal errors if not installed
try {
    $tema = $db->query("SELECT * FROM temas_personalizados WHERE ativo = 1 LIMIT 1")->fetch();
} catch (Exception $e) {
    // If table doesn't exist, provide default empty structure
    $tema = [];
}

// Cores padrão se não houver tema
$cores = [
    'primaria' => $tema['cor_primaria'] ?? '#2c3e50',
    'secundaria' => $tema['cor_secundaria'] ?? '#34495e',
    'destaque' => $tema['cor_destaque'] ?? '#3498db',
    'sucesso' => $tema['cor_sucesso'] ?? '#27ae60',
    'alerta' => $tema['cor_alerta'] ?? '#ffc107'
];

$fontes = [
    'titulo' => $tema['fonte_titulo'] ?? 'Segoe UI',
    'corpo' => $tema['fonte_corpo'] ?? 'Segoe UI'
];

// Salvar alterações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novoTema = [
        'cor_primaria' => $_POST['cor_primaria'],
        'cor_secundaria' => $_POST['cor_secundaria'],
        'cor_destaque' => $_POST['cor_destaque'],
        'cor_sucesso' => $_POST['cor_sucesso'],
        'cor_alerta' => $_POST['cor_alerta'],
        'fonte_titulo' => $_POST['fonte_titulo'],
        'fonte_corpo' => $_POST['fonte_corpo'],
        'tamanho_base' => $_POST['tamanho_base'],
        'espacamento_padrao' => $_POST['espacamento_padrao'],
        'bordas_arredondadas' => isset($_POST['bordas_arredondadas']) ? 1 : 0,
        'sombras' => isset($_POST['sombras']) ? 1 : 0,
        'css_custom' => $_POST['css_custom']
    ];
    
    // Atualizar no banco (Com Prepared Statements para evitar SQL Injection)
    $sql = "UPDATE temas_personalizados SET 
        cor_primaria = ?,
        cor_secundaria = ?,
        cor_destaque = ?,
        cor_sucesso = ?,
        cor_alerta = ?,
        fonte_titulo = ?,
        fonte_corpo = ?,
        tamanho_base = ?,
        espacamento_padrao = ?,
        bordas_arredondadas = ?,
        sombras = ?,
        css_custom = ?
        WHERE ativo = 1";
        
    $params = [
        $novoTema['cor_primaria'],
        $novoTema['cor_secundaria'],
        $novoTema['cor_destaque'],
        $novoTema['cor_sucesso'],
        $novoTema['cor_alerta'],
        $novoTema['fonte_titulo'],
        $novoTema['fonte_corpo'],
        $novoTema['tamanho_base'],
        $novoTema['espacamento_padrao'],
        $novoTema['bordas_arredondadas'],
        $novoTema['sombras'],
        $novoTema['css_custom']
    ];
    
    $db->query($sql, $params);
    
    // Gerar CSS dinâmico
    gerarCSSDinamico($novoTema);
    
    $mensagem = "✅ Tema atualizado com sucesso!";
    $tipoMensagem = "success";
    
    // Reload updated theme data
    try {
        $tema = $db->query("SELECT * FROM temas_personalizados WHERE ativo = 1 LIMIT 1")->fetch();
        // Update variables for display
        $cores = [
            'primaria' => $tema['cor_primaria'] ?? '#2c3e50',
            'secundaria' => $tema['cor_secundaria'] ?? '#34495e',
            'destaque' => $tema['cor_destaque'] ?? '#3498db',
            'sucesso' => $tema['cor_sucesso'] ?? '#27ae60',
            'alerta' => $tema['cor_alerta'] ?? '#ffc107'
        ];
        $fontes = [
            'titulo' => $tema['fonte_titulo'] ?? 'Segoe UI',
            'corpo' => $tema['fonte_corpo'] ?? 'Segoe UI'
        ];
    } catch (Exception $e) {}
}

/**
 * Gera arquivo CSS com tema personalizado
 */
function gerarCSSDinamico($tema) {
    $css = "/**
 * TEMA PERSONALIZADO - GEOMETRPOLE
 * Gerado automaticamente em " . date('d/m/Y H:i:s') . "
 */

:root {
    /* Cores da Marca */
    --cor-primaria: {$tema['cor_primaria']};
    --cor-secundaria: {$tema['cor_secundaria']};
    --cor-destaque: {$tema['cor_destaque']};
    --cor-sucesso: {$tema['cor_sucesso']};
    --cor-alerta: {$tema['cor_alerta']};
    
    /* Fontes */
    --fonte-titulo: '{$tema['fonte_titulo']}', sans-serif;
    --fonte-corpo: '{$tema['fonte_corpo']}', sans-serif;
    
    /* Tamanhos */
    --tamanho-base: {$tema['tamanho_base']};
    
    /* Espaçamentos */
    --espacamento-padrao: {$tema['espacamento_padrao']};
    
    /* Bordas */
    --raio-borda: " . ($tema['bordas_arredondadas'] ? '8px' : '0px') . ";
    
    /* Sombras */
    --sombra-padrao: " . ($tema['sombras'] ? '0 4px 6px rgba(0,0,0,0.1)' : 'none') . ";
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
{$tema['css_custom']}
";
    
    file_put_contents('../templates/css/tema-dinamico.css', $css);
}

// Lista de fontes disponíveis
$fontesDisponiveis = [
    'Segoe UI' => 'Segoe UI (Padrão Windows)',
    'Roboto' => 'Roboto (Google)',
    'Open Sans' => 'Open Sans (Google)',
    'Lato' => 'Lato (Google)',
    'Montserrat' => 'Montserrat (Google)',
    'Poppins' => 'Poppins (Google)',
    'Arial' => 'Arial',
    'Helvetica' => 'Helvetica',
    'Georgia' => 'Georgia (Serif)',
    'Times New Roman' => 'Times New Roman (Serif)'
];

// Tamanhos de fonte
$tamanhosFonte = ['9pt', '10pt', '11pt', '12pt', '13pt', '14pt'];

// Espaçamentos
$espacamentos = ['10px', '15px', '20px', '25px', '30px'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Personalização - GEOMETRPOLE</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        
        /* Layout */
        .admin-container {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            background: <?php echo $cores['primaria']; ?>;
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
        
        .nav-icon {
            font-size: 20px;
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
            color: <?php echo $cores['primaria']; ?>;
        }
        
        .btn-preview {
            background: <?php echo $cores['destaque']; ?>;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-preview:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        /* Cards */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .card-header {
            padding: 20px 25px;
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-header h2 {
            font-size: 18px;
            color: <?php echo $cores['primaria']; ?>;
        }
        
        .card-body {
            padding: 25px;
        }
        
        /* Form Elements */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: <?php echo $cores['destaque']; ?>;
        }
        
        /* Color Picker Custom */
        .color-picker-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        input[type="color"] {
            width: 50px;
            height: 40px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        
        .color-value {
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 13px;
        }
        
        /* Preview Box */
        .preview-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 30px;
            margin-top: 20px;
            border: 2px dashed #ddd;
        }
        
        .preview-title {
            text-align: center;
            color: #999;
            margin-bottom: 20px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Simulação da Proposta */
        .preview-proposta {
            background: white;
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .preview-header {
            text-align: center;
            border-bottom: 3px solid <?php echo $cores['primaria']; ?>;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .preview-header h3 {
            color: <?php echo $cores['primaria']; ?>;
            font-size: 20px;
            margin-bottom: 5px;
        }
        
        .preview-section {
            margin-bottom: 20px;
        }
        
        .preview-section-title {
            background: <?php echo $cores['primaria']; ?>;
            color: white;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 4px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .preview-service {
            background: #f8f9fa;
            border-left: 4px solid <?php echo $cores['destaque']; ?>;
            padding: 15px;
            margin: 10px 0;
        }
        
        .preview-service strong {
            color: <?php echo $cores['primaria']; ?>;
            font-size: 16px;
        }
        
        .preview-price {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px solid <?php echo $cores['primaria']; ?>;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        
        .preview-price-value {
            font-size: 24px;
            font-weight: 700;
            color: <?php echo $cores['primaria']; ?>;
        }
        
        /* Toggle Switch */
        .toggle-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .toggle {
            position: relative;
            width: 50px;
            height: 26px;
            background: #ccc;
            border-radius: 13px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .toggle.active {
            background: <?php echo $cores['sucesso']; ?>;
        }
        
        .toggle-slider {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            transition: all 0.3s;
        }
        
        .toggle.active .toggle-slider {
            left: 27px;
        }
        
        /* Botão Salvar */
        .btn-save {
            background: <?php echo $cores['sucesso']; ?>;
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
        }
        
        /* Alertas */
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
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
        
        /* CSS Editor */
        .css-editor {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.5;
            min-height: 200px;
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
        }
        
        /* Responsivo */
        @media (max-width: 768px) {
            .admin-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                🎯 GEOMETRPOLE
            </div>
            <div class="sidebar-version">v2.0.0 - Painel Admin</div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="painel.php" class="nav-link active">
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
                    <a href="backup.php" class="nav-link">
                        <i class="fas fa-archive"></i>
                        Backup & Restauração
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../admin_parametros.php" class="nav-link">
                        <i class="fas fa-list-check"></i>
                        Cadastros e Parâmetros
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
                <h1>🎨 Personalização do Tema</h1>
                <button class="btn-preview" onclick="atualizarPreview()">
                    👁️ Atualizar Preview
                </button>
            </div>
            
            <?php if (isset($mensagem)): ?>
                <div class="alert alert-success">
                    ✅ <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                
                <!-- Cores da Marca -->
                <div class="card">
                    <div class="card-header">
                        <h2>🎨 Cores da Marca</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Cor Primária (Cabeçalhos)</label>
                                <div class="color-picker-wrapper">
                                    <input type="color" name="cor_primaria" value="<?php echo $cores['primaria']; ?>" onchange="updateColor(this)">
                                    <span class="color-value"><?php echo $cores['primaria']; ?></span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Cor Secundária (Gradientes)</label>
                                <div class="color-picker-wrapper">
                                    <input type="color" name="cor_secundaria" value="<?php echo $cores['secundaria']; ?>" onchange="updateColor(this)">
                                    <span class="color-value"><?php echo $cores['secundaria']; ?></span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Cor de Destaque (Links/Botões)</label>
                                <div class="color-picker-wrapper">
                                    <input type="color" name="cor_destaque" value="<?php echo $cores['destaque']; ?>" onchange="updateColor(this)">
                                    <span class="color-value"><?php echo $cores['destaque']; ?></span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Cor de Sucesso (Confirmações)</label>
                                <div class="color-picker-wrapper">
                                    <input type="color" name="cor_sucesso" value="<?php echo $cores['sucesso']; ?>" onchange="updateColor(this)">
                                    <span class="color-value"><?php echo $cores['sucesso']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tipografia -->
                <div class="card">
                    <div class="card-header">
                        <h2>✏️ Tipografia</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Fonte dos Títulos</label>
                                <select name="fonte_titulo" class="form-control">
                                    <?php foreach ($fontesDisponiveis as $valor => $nome): ?>
                                        <option value="<?php echo $valor; ?>" <?php echo $fontes['titulo'] == $valor ? 'selected' : ''; ?>>
                                            <?php echo $nome; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Fonte do Corpo</label>
                                <select name="fonte_corpo" class="form-control">
                                    <?php foreach ($fontesDisponiveis as $valor => $nome): ?>
                                        <option value="<?php echo $valor; ?>" <?php echo $fontes['corpo'] == $valor ? 'selected' : ''; ?>>
                                            <?php echo $nome; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Tamanho Base</label>
                                <select name="tamanho_base" class="form-control">
                                    <?php foreach ($tamanhosFonte as $tamanho): ?>
                                        <option value="<?php echo $tamanho; ?>" <?php echo ($tema['tamanho_base'] ?? '11pt') == $tamanho ? 'selected' : ''; ?>>
                                            <?php echo $tamanho; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Espaçamento Padrão</label>
                                <select name="espacamento_padrao" class="form-control">
                                    <?php foreach ($espacamentos as $esp): ?>
                                        <option value="<?php echo $esp; ?>" <?php echo ($tema['espacamento_padrao'] ?? '15px') == $esp ? 'selected' : ''; ?>>
                                            <?php echo $esp; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Opções de Layout -->
                <div class="card">
                    <div class="card-header">
                        <h2>📐 Opções de Layout</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Bordas Arredondadas</label>
                                <div class="toggle-wrapper">
                                    <div class="toggle <?php echo ($tema['bordas_arredondadas'] ?? 1) ? 'active' : ''; ?>" onclick="toggleSwitch(this)">
                                        <div class="toggle-slider"></div>
                                    </div>
                                    <input type="hidden" name="bordas_arredondadas" value="<?php echo $tema['bordas_arredondadas'] ?? 1; ?>">
                                    <span><?php echo ($tema['bordas_arredondadas'] ?? 1) ? 'Ativado' : 'Desativado'; ?></span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Sombras nos Elementos</label>
                                <div class="toggle-wrapper">
                                    <div class="toggle <?php echo ($tema['sombras'] ?? 1) ? 'active' : ''; ?>" onclick="toggleSwitch(this)">
                                        <div class="toggle-slider"></div>
                                    </div>
                                    <input type="hidden" name="sombras" value="<?php echo $tema['sombras'] ?? 1; ?>">
                                    <span><?php echo ($tema['sombras'] ?? 1) ? 'Ativado' : 'Desativado'; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- CSS Customizado -->
                <div class="card">
                    <div class="card-header">
                        <h2>💻 CSS Customizado (Avançado)</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Adicione seu próprio CSS para personalizações avançadas</label>
                            <textarea name="css_custom" class="form-control css-editor" placeholder="/* Seu CSS aqui */"><?php echo htmlspecialchars($tema['css_custom'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Preview -->
                <div class="card">
                    <div class="card-header">
                        <h2>👁️ Preview da Proposta</h2>
                    </div>
                    <div class="card-body">
                        <div class="preview-box">
                            <div class="preview-title">Como ficará sua proposta</div>
                            <div class="preview-proposta" id="previewContainer">
                                <div class="preview-header">
                                    <h3>SUA EMPRESA</h3>
                                    <small>Proposta Técnica Comercial</small>
                                </div>
                                
                                <div class="preview-section">
                                    <div class="preview-section-title">Escopo do Serviço</div>
                                    <div class="preview-service">
                                        <strong>LEVANTAMENTO TOPOGRÁFICO COM DRONE</strong>
                                    </div>
                                </div>
                                
                                <div class="preview-section">
                                    <div class="preview-section-title">Investimento</div>
                                    <div class="preview-price">
                                        <div class="preview-price-value">R$ 3.250,00</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-save">
                    💾 Salvar Alterações do Tema
                </button>
                
            </form>
        </main>
    </div>
    
    <script>
        // Atualizar valor do color picker
        function updateColor(input) {
            input.nextElementSibling.textContent = input.value;
            atualizarPreview();
        }
        
        // Toggle switch
        function toggleSwitch(element) {
            element.classList.toggle('active');
            const input = element.nextElementSibling;
            const label = input.nextElementSibling;
            input.value = element.classList.contains('active') ? 1 : 0;
            label.textContent = element.classList.contains('active') ? 'Ativado' : 'Desativado';
            atualizarPreview();
        }
        
        // Preview ao vivo
        function atualizarPreview() {
            const primaria = document.querySelector('input[name="cor_primaria"]').value;
            const destaque = document.querySelector('input[name="cor_destaque"]').value;
            
            document.querySelector('.preview-header').style.borderBottomColor = primaria;
            document.querySelector('.preview-header h3').style.color = primaria;
            document.querySelectorAll('.preview-section-title').forEach(el => {
                el.style.background = primaria;
            });
            document.querySelector('.preview-service').style.borderLeftColor = destaque;
            document.querySelector('.preview-service strong').style.color = primaria;
            document.querySelector('.preview-price').style.borderColor = primaria;
            document.querySelector('.preview-price-value').style.color = primaria;
        }
    </script>
</body>
</html>
