<?php
define('SGT_PROPOSTAS', true);
require_once __DIR__ . '/config.php';

$tema_atual = $_GET['preview'] ?? 'classico';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>SGT Propostas - Sistema de Temas</title>
    <style>
        body { font-family: Inter, sans-serif; padding: 40px; background: #f3f4f6; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #1f2937; margin-bottom: 8px; }
        .subtitle { color: #6b7280; margin-bottom: 32px; }
        
        .tema-selector {
            display: flex;
            gap: 16px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }
        
        .tema-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            width: 280px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
        }
        
        .tema-card:hover { border-color: #3b82f6; transform: translateY(-2px); }
        .tema-card.active { border-color: #3b82f6; background: #eff6ff; }
        
        .tema-nome { font-size: 18px; font-weight: 600; margin-bottom: 4px; }
        .tema-desc { font-size: 14px; color: #6b7280; }
        .tema-cor { 
            width: 40px; 
            height: 40px; 
            border-radius: 8px; 
            margin-top: 12px;
            border: 2px solid #e5e7eb;
        }
        
        .preview-frame {
            width: 100%;
            height: 800px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        
        .code-block {
            background: #1f2937;
            color: #e5e7eb;
            padding: 20px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 13px;
            overflow-x: auto;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎨 SGT Propostas - Sistema de Temas</h1>
        <p class="subtitle">Selecione um tema para visualizar a proposta</p>
        
        <div class="tema-selector">
            <?php foreach ($TEMAS as $key => $tema): 
                $cores = [
                    'classico' => '#b45f06',
                    'drone' => '#0ea5e9',
                    'moderno' => '#18181b',
                ];
            ?>
                <a href="?preview=<?= $key ?>" class="tema-card <?= $tema_atual === $key ? 'active' : '' ?>">
                    <div class="tema-nome"><?= $tema['nome'] ?></div>
                    <div class="tema-desc"><?= $tema['descricao'] ?></div>
                    <div class="tema-cor" style="background: <?= $cores[$key] ?>"></div>
                </a>
            <?php endforeach; ?>
        </div>
        
        <h3>Preview ao vivo:</h3>
        <iframe src="gerar-proposta.php?id=98&tema=<?= $tema_atual ?>" class="preview-frame"></iframe>
        
        <h3>URL para integração:</h3>
        <div class="code-block">
            // Auto-detecta pelo tipo de serviço<br>
            $url = "https://seusite.com/gerar-proposta.php?id=98";<br><br>
            
            // Força tema específico<br>
            $url = "https://seusite.com/gerar-proposta.php?id=98&tema=<?= $tema_atual ?>";
        </div>
        
        <h3>Detecção automática de temas:</h3>
        <div class="code-block">
            <?php foreach ($MAPA_SERVICOS as $servico => $tema): ?>
                "<?= $servico ?>" => "<?= $tema ?>"<br>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
