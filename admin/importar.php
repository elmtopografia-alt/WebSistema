<?php
/**
 * IMPORTAR TEMA - GEOMETRPOLE
 * Recebe arquivo JSON e adiciona aos temas
 */

require_once '../config/config.php';
require_once '../config/database.php';

// Autenticação
require_once 'auth_check.php';

session_start();
$db = new Database();

$mensagem = '';
$tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo_tema'])) {
    $arquivo = $_FILES['arquivo_tema'];
    
    if ($arquivo['error'] === UPLOAD_ERR_OK) {
        $conteudo = file_get_contents($arquivo['tmp_name']);
        $dados = json_decode($conteudo, true);
        
        if ($dados && isset($dados['tema'])) {
            $t = $dados['tema'];
            
            // Verificar se slug já existe
            $slugBase = $t['slug'] . '_importado';
            $slug = $slugBase;
            $contador = 1;
            
            while ($db->query("SELECT id FROM temas_personalizados WHERE slug = ?", [$slug])->fetch()) {
                $slug = $slugBase . '_' . $contador++;
            }
            
            $cores = $t['cores'] ?? [];
            $tipografia = $t['tipografia'] ?? [];
            $layout = $t['layout'] ?? [];

            // Sanitizar CSS customizado (básico)
            $cssCustom = $t['css_custom'] ?? '';
            
            // Inserir novo tema
            $sql = "INSERT INTO temas_personalizados 
                (nome, slug, descricao, icone, cor_primaria, cor_secundaria, cor_destaque,
                 cor_sucesso, cor_alerta, fonte_titulo, fonte_corpo, tamanho_base,
                 espacamento_padrao, bordas_arredondadas, sombras, css_custom, ativo)
                VALUES 
                (?, ?, ?, ?,
                 ?, ?, ?,
                 ?, ?, ?,
                 ?, ?,
                 ?, ?,
                 ?, ?, 0)";
            
            $params = [
                $t['nome'] . ' (Importado)', $slug, $t['descricao'], $t['icone'],
                $cores['primaria'], $cores['secundaria'], $cores['destaque'],
                $cores['sucesso'], $cores['alerta'], $tipografia['titulo'],
                $tipografia['corpo'], $tipografia['tamanho_base'],
                $layout['espacamento'], ($layout['bordas_arredondadas'] ?? true) ? 1 : 0,
                ($layout['sombras'] ?? true) ? 1 : 0, $cssCustom
            ];
            
            try {
                $db->query($sql, $params);
                $mensagem = "✅ Tema '{$t['nome']}' importado com sucesso! Ative-o quando desejar.";
                $tipo = "success";
            } catch (Exception $e) {
                $mensagem = "❌ Erro ao importar: " . $e->getMessage();
                $tipo = "error";
            }
        } else {
            $mensagem = "❌ Arquivo inválido ou corrompido";
            $tipo = "error";
        }
    } else {
        $mensagem = "❌ Erro no upload do arquivo";
        $tipo = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Importar Tema - GEOMETRPOLE</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        
        .import-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 90%;
            text-align: center;
        }
        
        .import-icon {
            font-size: 64px;
            color: #3498db;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        p {
            color: #7f8c8d;
            margin-bottom: 30px;
        }
        
        .upload-area {
            border: 3px dashed #ddd;
            border-radius: 8px;
            padding: 40px;
            margin-bottom: 20px;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .upload-area:hover {
            border-color: #3498db;
            background: #f8f9fa;
        }
        
        .upload-area i {
            font-size: 48px;
            color: #bdc3c7;
            margin-bottom: 15px;
        }
        
        input[type="file"] {
            display: none;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
            margin-left: 10px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="import-container">
        <div class="import-icon">
            <i class="fas fa-file-import"></i>
        </div>
        
        <h1>Importar Tema</h1>
        <p>Selecione um arquivo .json exportado de outra instalação GEOMETRPOLE</p>
        
        <?php if ($mensagem): ?>
            <div class="alert alert-<?php echo $tipo; ?>">
                <?php echo $mensagem; ?>
                <div style="margin-top: 15px;">
                    <a href="temas.php" class="btn btn-primary">Voltar para Temas</a>
                </div>
            </div>
        <?php else: ?>
            <form method="POST" enctype="multipart/form-data" id="importForm">
                <label class="upload-area" onclick="document.getElementById('arquivo').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <div>Clique para selecionar ou arraste o arquivo aqui</div>
                    <small style="color: #999;">Apenas arquivos .json</small>
                </label>
                <input type="file" name="arquivo_tema" id="arquivo" accept=".json" required onchange="updateFileName(this)">
                
                <div id="fileName" style="margin-bottom: 20px; color: #2c3e50; font-weight: 500;"></div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Importar Tema
                </button>
                <a href="temas.php" class="btn btn-secondary">Cancelar</a>
            </form>
        <?php endif; ?>
    </div>
    
    <script>
        function updateFileName(input) {
            if (input.files && input.files[0]) {
                document.getElementById('fileName').textContent = 'Arquivo: ' + input.files[0].name;
            }
        }
        
        // Drag and drop
        const uploadArea = document.querySelector('.upload-area');
        if (uploadArea) {
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.style.borderColor = '#3498db';
                uploadArea.style.background = '#f8f9fa';
            });
            
            uploadArea.addEventListener('dragleave', () => {
                uploadArea.style.borderColor = '#ddd';
                uploadArea.style.background = 'white';
            });
            
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                const files = e.dataTransfer.files;
                if (files.length) {
                    document.getElementById('arquivo').files = files;
                    updateFileName(document.getElementById('arquivo'));
                }
            });
        }
    </script>
</body>
</html>
