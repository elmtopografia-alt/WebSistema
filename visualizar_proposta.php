<?php
/**
 * VISUALIZAR PROPOSTA - SGT Propostas v2
 * Visualização final limpa (sem controles de edição)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/ConnectionManager.php';
require_once __DIR__ . '/core/TemaEngine.php';
require_once __DIR__ . '/core/ModeloBase.php';
require_once __DIR__ . '/ResolvedorChavesSistema.php';

use SGT\Core\TemaEngine;

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'SGT\\Modelos\\';
    $baseDir = __DIR__ . '/modelos/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) require $file;
});

// Buscar proposta
$id = $_GET['id'] ?? null;
$formato = $_GET['formato'] ?? 'html'; // html | pdf | print

if (!$id) die("ID não informado");

try {
    $conn = ConnectionManager::get();
    $stmt = $conn->prepare("SELECT * FROM Propostas WHERE id_proposta = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $proposta = $res->fetch_assoc();
    
    if (!$proposta) die("Proposta não encontrada");
    
} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}

// Renderizar
$modeloNome = $proposta['modelo_docx'] ?? 'PropostaDrone';
$cor = $proposta['cor'] ?? 'verde';
$dados = json_decode($proposta['dados_manual'] ?? '{}', true);

try {
    $classe = "SGT\\Modelos\\{$modeloNome}";
    $resolvedor = new ResolvedorChavesSistema();
    $modelo = new $classe($cor);
    $html = $modelo->render($dados, $resolvedor, $proposta['id_criador'] ?? 1);
    
} catch (Exception $e) {
    die("Erro ao renderizar: " . $e->getMessage());
}

// Saída conforme formato
if ($formato === 'print') {
    // Versão para impressão
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Proposta #<?= $id ?></title>
        <link rel="stylesheet" href="temas/tema.php?cor=<?= $cor ?>">
        <style>
            body { margin: 0; padding: 20px; }
            .no-print { display: none !important; }
            @media print {
                body { padding: 0; }
                .sgt-proposta { max-width: 100% !important; }
            }
        </style>
    </head>
    <body>
        <?= $html ?>
        <div class="no-print" style="margin-top: 30px; text-align: center;">
            <button onclick="window.print()" style="padding: 10px 20px;">🖨️ Imprimir</button>
            <button onclick="window.close()" style="padding: 10px 20px;">Fechar</button>
        </div>
    </body>
    </html>
    <?php
    
} elseif ($formato === 'pdf') {
    // Redirecionar para gerador de PDF (se existir)
    // ou usar biblioteca como DomPDF
    header('Location: gerar_pdf.php?id=' . $id);
    exit;
    
} else {
    // Visualização padrão HTML
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Proposta #<?= $id ?> - Visualização</title>
        <link rel="stylesheet" href="temas/tema.php?cor=<?= $cor ?>">
        <style>
            body { 
                font-family: 'Segoe UI', sans-serif; 
                margin: 0; 
                padding: 20px; 
                background: #f3f4f6;
            }
            .container { max-width: 1200px; margin: 0 auto; }
            
            .toolbar {
                background: white;
                padding: 15px 20px;
                border-radius: 8px;
                margin-bottom: 20px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 10px;
            }
            
            .toolbar h1 { margin: 0; font-size: 1.25rem; color: #1f2937; }
            .toolbar-info { color: #6b7280; font-size: 0.875rem; }
            
            .acoes { display: flex; gap: 10px; }
            
            .btn {
                padding: 8px 16px;
                border-radius: 6px;
                text-decoration: none;
                font-size: 14px;
                font-weight: 500;
                display: inline-flex;
                align-items: center;
                gap: 5px;
            }
            
            .btn-primary { background: #3b82f6; color: white; }
            .btn-secondary { background: #f3f4f6; color: #374151; }
            .btn-success { background: #10b981; color: white; }
            .btn-warning { background: #f59e0b; color: white; }
            
            .preview-box {
                background: white;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                overflow: hidden;
            }
            
            .tema-badge {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 4px 12px;
                background: <?= (new TemaEngine($cor))->getPaleta()['fundo'] ?>;
                color: <?= (new TemaEngine($cor))->getPaleta()['texto'] ?>;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 600;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="toolbar">
                <div>
                    <h1>📄 Proposta #<?= $id ?></h1>
                    <div class="toolbar-info">
                        Criada em <?= date('d/m/Y', strtotime($proposta['data_criacao'])) ?> | 
                        Status: <strong><?= ucfirst($proposta['status'] ?? 'rascunho') ?></strong>
                        <span class="tema-badge">
                            <?= (new TemaEngine($cor))->getPaleta()['nome'] ?>
                        </span>
                    </div>
                </div>
                
                <div class="acoes">
                    <a href="?id=<?= $id ?>&formato=print" target="_blank" class="btn btn-secondary">
                        🖨️ Imprimir
                    </a>
                    <a href="editar_proposta.php?id=<?= $id ?>" class="btn btn-warning">
                        ✏️ Editar
                    </a>
                    <a href="lista_propostas.php" class="btn btn-secondary">
                        ← Lista
                    </a>
                </div>
            </div>
            
            <div class="preview-box">
                <?= $html ?>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>
