<?php
/**
 * gerador_update_docs.php 
 * Versão DEFINITIVA - "ABRIR A PORTA"
 * V4.2 - Foco em rapidez e zero cliques desnecessários.
 */

// Silenciamos erros para manter a interface limpa, mas registramos se necessário
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'config.php';
require_once 'db.php';
require_once 'funcoes_gerador_docs.php';

define('UPLOAD_DIR', __DIR__ . '/uploads_temp/');
define('MODELOS_DIR_TESTE', __DIR__ . '/teste/');

// Garantir diretórios
foreach (array(UPLOAD_DIR, MODELOS_DIR_TESTE) as $dir) {
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
}

// Processamento AJAX Simplificado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['docx'])) {
    header('Content-Type: application/json');
    $arquivo = $_FILES['docx'];
    
    $pythonCmd = (stripos(PHP_OS, 'WIN') === 0) ? 'python' : 'python3';
    $pythonScript = __DIR__ . '/conversor_docx.py';
    
    // Caminho temporário seguro
    $caminho = UPLOAD_DIR . uniqid('sgt_') . '.docx';
    
    if (!move_uploaded_file($arquivo['tmp_name'], $caminho)) {
        echo json_encode(['erro' => 'Falha ao mover arquivo temporário.']);
        exit;
    }
    
    // Execução do Conversor Python
    $comando = escapeshellcmd($pythonCmd . " " . escapeshellarg($pythonScript) . " " . escapeshellarg($caminho));
    $saida = shell_exec($comando . " 2>&1");
    @unlink($caminho);
    
    $resultado = json_decode($saida, true);
    if ($resultado && isset($resultado['sucesso']) && $resultado['sucesso']) {
        
        // Nome limpo para a classe PHP
        $nomeBase = preg_replace('/[^a-zA-Z0-9]/', '', str_replace('.docx', '', $arquivo['name']));
        if (empty($nomeBase)) $nomeBase = "Doc_" . date('His');
        $nomeModelo = "Docs_" . $nomeBase;
        
        // Geração do Código via funcoes_gerador_docs.php
        $novoCodigo = gerarCodigoPHP($resultado, $nomeModelo);
        $nomeArquivoFinal = 'Modelo' . $nomeModelo . '.php';
        $caminhoFinal = MODELOS_DIR_TESTE . $nomeArquivoFinal;
        
        if (file_put_contents($caminhoFinal, $novoCodigo)) {
            echo json_encode([
                'sucesso' => true,
                'msg' => "O modelo PHP foi criado instantaneamente.",
                'loc' => "F:/Site/Sistema Proposta/SistemaWeb/teste/$nomeArquivoFinal",
                'nome' => $nomeArquivoFinal
            ]);
        } else {
            echo json_encode(['erro' => 'Erro de permissão ao salvar na pasta /teste/']);
        }
    } else {
        echo json_encode([
            'erro' => 'Não conseguimos ler a estrutura deste arquivo.',
            'debug' => $saida
        ]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT - Conversor Direto DOCS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css">
    <style>
        :root { --brand: #3b82f6; --bg: #0b0f1a; --glass: rgba(255, 255, 255, 0.03); }
        body { background: var(--bg); color: #f8fafc; font-family: 'Inter', system-ui, sans-serif; height: 100vh; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        
        /* Glassmorphism SGT Theme */
        .portal-card { 
            background: var(--glass); 
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08); 
            border-radius: 24px; 
            padding: 50px; 
            width: 100%; 
            max-width: 550px; 
            text-align: center;
            box-shadow: 0 40px 100px -20px rgba(0,0,0,0.8);
            position: relative;
        }

        .icon-box {
            width: 80px; height: 80px; background: rgba(59, 130, 246, 0.1);
            border-radius: 20px; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 30px; color: var(--brand); font-size: 40px;
        }

        h2 { font-weight: 800; letter-spacing: -0.02em; margin-bottom: 10px; }
        p.desc { opacity: 0.6; font-size: 0.95rem; line-height: 1.5; margin-bottom: 40px; }

        .btn-portal {
            background: var(--brand); color: white; border: none; padding: 18px 40px;
            border-radius: 16px; font-weight: 700; font-size: 1.1rem; width: 100%;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex; align-items: center; justify-content: center; gap: 12px;
            box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.4);
        }
        .btn-portal:hover { background: #2563eb; transform: scale(1.02); color: white; }
        .btn-portal:active { transform: scale(0.98); }

        .result-box { 
            margin-top: 30px; padding: 20px; border-radius: 16px; 
            display: none; animation: slideUp 0.5s ease-out;
            text-align: left;
        }
        .result-success { background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); }
        .result-error { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); }

        .path-display { 
            font-family: 'Fira Code', monospace; font-size: 11px; 
            background: rgba(0,0,0,0.3); padding: 10px; border-radius: 8px;
            word-break: break-all; margin-top: 10px; color: #94a3b8;
        }

        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .loader { width: 24px; height: 24px; border: 3px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; display: none; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

    <div class="portal-card">
        <div class="icon-box">
            <i class="ph ph-file-doc"></i>
        </div>
        
        <h2>Abrir a Porta</h2>
        <p class="desc">Basta escolher o arquivo <b>DOCX</b> que você vê na sua pasta sincronizada do Drive. O modelo PHP será gerado na hora.</p>

        <input type="file" id="file-input" class="d-none" accept=".docx" onchange="processar(this.files[0])">
        
        <button class="btn-portal" id="btn-main" onclick="document.getElementById('file-input').click()">
            <span class="btn-text">Selecionar do Drive e Gerar</span>
            <div class="loader"></div>
        </button>

        <div id="result" class="result-box">
            <div id="result-msg" style="font-weight: 600; font-size: 0.9rem;"></div>
            <div class="path-display" id="result-path"></div>
        </div>

        <div class="mt-4 text-muted" style="font-size: 0.8rem; opacity: 0.4;">
            <i class="ph ph-folder"></i> Destino: <b>/SistemaWeb/teste/</b>
        </div>
    </div>

    <script>
        function processar(file) {
            if(!file) return;
            
            const btn = document.getElementById('btn-main');
            const loader = btn.querySelector('.loader');
            const text = btn.querySelector('.btn-text');
            const result = document.getElementById('result');
            const resultMsg = document.getElementById('result-msg');
            const resultPath = document.getElementById('result-path');

            // UI State
            btn.disabled = true;
            loader.style.display = 'block';
            text.style.display = 'none';
            result.style.display = 'none';

            const fd = new FormData();
            fd.append('docx', file);

            fetch('', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                loader.style.display = 'none';
                text.style.display = 'block';
                result.style.display = 'block';

                if(data.sucesso) {
                    result.className = 'result-box result-success';
                    resultMsg.innerHTML = '<i class="ph ph-check-circle text-success me-2"></i> ' + data.msg;
                    resultPath.innerHTML = data.loc;
                    // Reset input
                    document.getElementById('file-input').value = '';
                } else {
                    result.className = 'result-box result-error';
                    resultMsg.innerHTML = '<i class="ph ph-warning-circle text-danger me-2"></i> ' + data.erro;
                    resultPath.innerHTML = data.debug || 'Verifique o formato do arquivo.';
                }
            })
            .catch(e => {
                btn.disabled = false;
                loader.style.display = 'none';
                text.style.display = 'block';
                result.style.display = 'block';
                result.className = 'result-box result-error';
                resultMsg.innerHTML = '❌ Erro de comunicação com o servidor.';
            });
        }
    </script>
</body>
</html>
