<?php
/**
 * gerador_upload_docx.php 
 * Versão Ultra-Compatível SGT (Resolve erros de sintaxe em XAMPP antigo)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug de erros fatais
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))) {
        echo "<div style='background:#fee2e2; border:2px solid #ef4444; color:#991b1b; padding:20px; border-radius:8px; margin:20px; font-family:sans-serif;'>";
        echo "<h3>🛑 Erro Crítico Detectado</h3>";
        echo "<pre>" . htmlspecialchars($error['message']) . "</pre>";
        echo "<p>Local: <code>" . basename($error['file']) . "</code> linha " . $error['line'] . "</p>";
        echo "</div>";
    }
});

require_once 'config.php';
require_once 'db.php';

define('UPLOAD_DIR', __DIR__ . '/uploads_temp/');
$pythonCmd = (stripos(PHP_OS, 'WIN') === 0) ? 'python' : 'python3';
define('PYTHON_EXE', $pythonCmd); 
define('PYTHON_SCRIPT', __DIR__ . '/conversor_docx.py');
define('MODELOS_DIR', __DIR__ . '/modelos_gerados/');

foreach (array(UPLOAD_DIR, MODELOS_DIR) as $dir) {
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
}

// Início do Processamento AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['docx'])) {
    header('Content-Type: application/json');
    $arquivo = $_FILES['docx'];
    $ext = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    if (strtolower($ext) !== 'docx') {
        echo json_encode(array('erro' => 'Apenas arquivos .docx são permitidos'));
        exit;
    }
    $nomeUnico = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $arquivo['name']);
    $caminho = UPLOAD_DIR . $nomeUnico;
    move_uploaded_file($arquivo['tmp_name'], $caminho);
    $comando = escapeshellcmd(PYTHON_EXE . " " . escapeshellarg(PYTHON_SCRIPT) . " " . escapeshellarg($caminho));
    $saida = shell_exec($comando . " 2>&1");
    if (file_exists($caminho)) @unlink($caminho);
    $resultado = json_decode($saida, true);
    if ($resultado && isset($resultado['sucesso']) && $resultado['sucesso']) {
        echo json_encode($resultado);
    } else {
        echo json_encode(array(
            'erro' => isset($resultado['erro']) ? $resultado['erro'] : 'Erro na conversão Python.',
            'debug' => $saida
        ));
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'gerar_php') {
    header('Content-Type: application/json');
    $dados = json_decode($_POST['dados'], true);
    $nomeModelo = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['nome_modelo']);
    if (empty($nomeModelo)) {
        echo json_encode(array('erro' => 'Nome inválido'));
        exit;
    }
    $codigo = gerarCodigoPHP($dados, $nomeModelo);
    $caminhoFinal = MODELOS_DIR . 'Modelo' . $nomeModelo . '.php';
    if (file_put_contents($caminhoFinal, $codigo)) {
        echo json_encode(array('sucesso' => true, 'arquivo' => 'Modelo' . $nomeModelo . '.php'));
    } else {
        echo json_encode(array('erro' => 'Erro ao salvar arquivo'));
    }
    exit;
}

function gerarCodigoPHP($dados, $nomeClasseSuffix) {
    $nomeClasse = "Modelo" . $nomeClasseSuffix;
    $data = date('d/m/Y H:i');
    $blocosStr = var_export($dados['blocos'], true);
    $varsGerais = var_export($dados['variaveis'], true);
    $cssGeral = addslashes($dados['css_geral']);

    // NOTA: CODE; deve estar na coluna 0 para compatibilidade com PHP < 7.3
    $template = <<<'CODE'
<?php
/**
 * MODELO GERADO AUTOMATICAMENTE - SGT DOCX Parser
 * Fonte: {{NOME_ARQUIVO}}
 */

namespace SGT\Propostas;

require_once __DIR__ . '/../ResolvedorChavesSistema.php';

class {{NOME_CLASSE}} 
{
    const NOME = '{{NOME_SUFFIX}}';
    
    private $blocos;
    private $variaveisDetectadas;
    private $cssCustom;

    public function __construct() {
        $this->blocos = {{BLOCOS}};
        $this->variaveisDetectadas = {{VARS}};
        $this->cssCustom = "{{CSS}}";
    }

    public function getConfig()
    {
        return array(
            'nome' => self::NOME,
            'blocos' => $this->blocos,
            'variaveis' => $this->variaveisDetectadas,
            'css' => $this->cssCustom
        );
    }

    public function render($dadosManuais, $resolvedor, $id_usuario)
    {
        $dadosSistema = $resolvedor->resolver($this->variaveisDetectadas, $id_usuario, $dadosManuais);
        $contexto = array_merge($dadosManuais, $dadosSistema);
        
        $html = "<div class='modelo-docx-container'>";
        $html .= "<style>{$this->cssCustom}</style>";
        
        foreach ($this->blocos as $bloco) {
            $html .= $this->renderBloco($bloco, $contexto);
        }
        
        $html .= "</div>";
        return $html;
    }

    private function renderBloco($bloco, $contexto)
    {
        if ($bloco['tipo'] === 'texto') {
            $tag = ($bloco['nivel_titulo'] > 0) ? 'h' . $bloco['nivel_titulo'] : 'p';
            $conteudo = $bloco['conteudo'];
            
            foreach ($bloco['variaveis'] as $var) {
                $valor = isset($contexto[$var]) ? $contexto[$var] : "[{$var}]";
                $pattern = '/(\$\{\s*' . preg_quote($var, '/') . '\s*\}|\{\{\s*' . preg_quote($var, '/') . '\s*\}\})/';
                $conteudo = preg_replace($pattern, $valor, $conteudo);
            }
            
            $estilos = $this->mapEstilos($bloco['estilos_css']);
            return "<$tag style='$estilos'>$conteudo</$tag>";
        }
        
        if ($bloco['tipo'] === 'tabela') {
            $html = "<table style='width:100%; border-collapse:collapse; border: 1px solid #dee2e6; margin-bottom: 25px;'>";
            foreach ($bloco['linhas'] as $i => $linha) {
                $html .= "<tr>";
                foreach ($linha as $celula) {
                    $tag = ($i === 0) ? 'th' : 'td';
                    $texto = $celula['texto'];
                    foreach ($bloco['variaveis'] as $var) {
                         $valor = isset($contexto[$var]) ? $contexto[$var] : "[{$var}]";
                         $pattern = '/(\$\{\s*' . preg_quote($var, '/') . '\s*\}|\{\{\s*' . preg_quote($var, '/') . '\s*\}\})/';
                         $texto = preg_replace($pattern, $valor, $texto);
                    }
                    $estilos = $this->mapEstilos($celula['estilos']);
                    $html .= "<$tag colspan='{$celula['colspan']}' style='$estilos'>$texto</$tag>";
                }
                $html .= "</tr>";
            }
            $html .= "</table>";
            return $html;
        }
        return "";
    }

    private function mapEstilos($estilos) {
        $out = "";
        foreach ($estilos as $k => $v) $out .= "$k:$v; ";
        return $out;
    }
}
CODE;

    $out = str_replace(
        array('{{NOME_ARQUIVO}}', '{{DATA}}', '{{NOME_CLASSE}}', '{{NOME_SUFFIX}}', '{{BLOCOS}}', '{{VARS}}', '{{CSS}}'),
        array($dados['nome_arquivo'], $data, $nomeClasse, $nomeClasseSuffix, $blocosStr, $varsGerais, $cssGeral),
        $template
    );
    return $out;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SGT - Gerador DOCX Ultra-Compatível</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --brand: #f97316; --bg: #0a0f1a; --glass: rgba(255, 255, 255, 0.05); }
        body { background: var(--bg); color: #f8fafc; font-family: sans-serif; }
        .glass-card { background: var(--glass); border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 25px; margin-bottom: 20px; }
        .upload-zone { border: 2px dashed rgba(255,255,255,0.2); border-radius: 12px; padding: 40px; text-align: center; cursor: pointer; }
        .btn-sgt { background: var(--brand); color: white; border: none; font-weight: 600; padding: 10px 20px; border-radius: 8px; }
        .preview-area { background: white; color: #333; padding: 30px; border-radius: 8px; max-height: 500px; overflow-y: auto; }
        .var-badge { background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 11px; margin: 2px; display: inline-block; }
    </style>
</head>
<body class="py-5">
    <div class="container" style="max-width: 800px;">
        <div class="text-center mb-5"><h1>📄 Gerador DOCX (Modo Seguro)</h1></div>
        <div id="step-1" class="glass-card">
            <div class="upload-zone" onclick="document.getElementById('file-input').click()">
                <div class="fs-1">📥</div>
                <p>Arraste seu .docx ou clique para selecionar</p>
                <input type="file" id="file-input" class="d-none" accept=".docx" onchange="uploadDocx(this.files[0])">
            </div>
            <div id="upload-error" class="alert alert-danger mt-3 d-none"></div>
            <div id="upload-status" class="mt-3 text-center d-none"><div class="spinner-border text-warning spinner-border-sm"></div> Analisando...</div>
        </div>
        <div id="step-2" class="glass-card d-none">
            <div class="d-flex justify-content-between mb-4">
                <h4>Revisar e Gerar</h4>
                <button class="btn btn-sgt" onclick="gerarPHP()">⚡ Gerar PHP</button>
            </div>
            <input type="text" id="model-name" class="form-control bg-dark text-white border-secondary mb-3">
            <div id="vars-list" class="mb-3"></div>
            <div class="preview-area" id="preview-html"></div>
        </div>
        <div id="step-3" class="glass-card d-none text-center py-5">
            <h2>🚀 Pronto!</h2>
            <p id="file-path-msg" class="text-muted"></p>
            <a href="editor_dinamico.php" class="btn btn-sgt">Ir para o Editor</a>
        </div>
    </div>
    <script>
        let analysisData = null;
        function uploadDocx(file) {
            if(!file) return;
            document.getElementById('upload-status').classList.remove('d-none');
            const fd = new FormData(); fd.append('docx', file);
            fetch('', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                document.getElementById('upload-status').classList.add('d-none');
                if(data.erro) { alert(data.erro); return; }
                analysisData = data;
                document.getElementById('step-1').classList.add('d-none');
                document.getElementById('step-2').classList.remove('d-none');
                document.getElementById('preview-html').innerHTML = data.html_preview;
                document.getElementById('model-name').value = data.nome_arquivo.replace('.docx', '').replace(/[^a-zA-Z0-9]/g, '_');
                const vBox = document.getElementById('vars-list');
                vBox.innerHTML = '';
                data.variaveis.forEach(v => { vBox.innerHTML += `<span class="var-badge">${v}</span>`; });
            }).catch(e => { alert("Erro fatal no upload. Verifique o console."); console.error(e); });
        }
        function gerarPHP() {
            const name = document.getElementById('model-name').value;
            const fd = new FormData(); fd.append('acao', 'gerar_php'); fd.append('nome_modelo', name); fd.append('dados', JSON.stringify(analysisData));
            fetch('', { method: 'POST', body: fd }).then(r => r.json()).then(data => {
                if(data.erro) { alert(data.erro); return; }
                document.getElementById('step-2').classList.add('d-none');
                document.getElementById('step-3').classList.remove('d-none');
                document.getElementById('file-path-msg').innerText = "Salvo em: modelos_gerados/" + data.arquivo;
            });
        }
    </script>
</body>
</html>
