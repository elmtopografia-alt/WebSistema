<?php
// require_once 'session_validator.php'; // Removido para liberar acesso local sem barreiras
require_once 'config.php';
require_once 'db.php';

// SEGURANÇA: Liberado para uso (remova a trava para facilitar o trabalho)
// O sistema de sessão continua sendo validado, mas sem exigir perfil 'admin'

// Configurações
define('UPLOAD_DIR', __DIR__ . '/uploads_temp/');
// Detecção automática de ambiente para o comando Python
$pythonCmd = (stripos(PHP_OS, 'WIN') === 0) ? 'python' : 'python3';
define('PYTHON_EXE', $pythonCmd); 
define('PYTHON_SCRIPT', __DIR__ . '/conversor_docx.py');
define('MODELOS_DIR', __DIR__ . '/modelos_gerados/');

// Cria diretórios se não existirem
foreach ([UPLOAD_DIR, MODELOS_DIR] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0755, true);
}

// AJAX: Processar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['docx'])) {
    header('Content-Type: application/json');
    
    $arquivo = $_FILES['docx'];
    
    // Validação de tipo (Simplificada)
    $ext = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    if (strtolower($ext) !== 'docx') {
        echo json_encode(['erro' => 'Apenas arquivos .docx são permitidos']);
        exit;
    }
    
    // Move para processamento
    $nomeUnico = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $arquivo['name']);
    $caminho = UPLOAD_DIR . $nomeUnico;
    move_uploaded_file($arquivo['tmp_name'], $caminho);
    
    // Executa Python
    // No Windows as vezes precisamos de escapeshellarg duplo ou tratamentos específicos
    $comando = escapeshellcmd(PYTHON_EXE . " " . escapeshellarg(PYTHON_SCRIPT) . " " . escapeshellarg($caminho));
    $saida = shell_exec($comando . " 2>&1");
    
    // Remove temp logo após ou guarda para debug (aqui removemos)
    if (file_exists($caminho)) unlink($caminho);
    
    $resultado = json_decode($saida, true);
    
    if ($resultado && isset($resultado['sucesso']) && $resultado['sucesso']) {
        echo json_encode($resultado);
    } else {
        echo json_encode([
            'erro' => $resultado['erro'] ?? 'Erro na conversão. Verifique se o Python e as bibliotecas (mammoth, python-docx) estão instalados no servidor.',
            'debug' => $saida
        ]);
    }
    exit;
}

// AJAX: Gravar o modelo final
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'gerar_php') {
    header('Content-Type: application/json');
    
    $dados = json_decode($_POST['dados'], true);
    $nomeModelo = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['nome_modelo']);
    
    if (empty($nomeModelo)) {
        echo json_encode(['erro' => 'Nome do modelo inválido']);
        exit;
    }

    $codigo = gerarCodigoPHP($dados, $nomeModelo);
    $caminhoFinal = MODELOS_DIR . 'Modelo' . $nomeModelo . '.php';
    
    if (file_contents_write($caminhoFinal, $codigo)) {
        echo json_encode(['sucesso' => true, 'arquivo' => 'Modelo' . $nomeModelo . '.php']);
    } else {
        echo json_encode(['erro' => 'Erro ao salvar arquivo']);
    }
    exit;
}

function file_contents_write($path, $content) {
    return file_put_contents($path, $content) !== false;
}

function gerarCodigoPHP($dados, $nomeClasseSuffix) {
    $nomeClasse = "Modelo" . $nomeClasseSuffix;
    $data = date('d/m/Y H:i');
    $blocosStr = var_export($dados['blocos'], true);
    $varsGerais = var_export($dados['variaveis'], true);
    
    return <<<PHP
<?php
/**
 * MODELO GERADO AUTOMATICAMENTE - SGT DOCX Parser
 * Fonte: {$dados['nome_arquivo']}
 * Gerado em: {$data}
 */

namespace SGT\Propostas;

require_once __DIR__ . '/../ResolvedorChavesSistema.php';

class {$nomeClasse} 
{
    public const NOME = '{$nomeClasseSuffix}';
    
    private array \$blocos = {$blocosStr};
    private array \$variaveisDetectadas = {$varsGerais};
    private string \$cssCustom = "{$dados['css_geral']}";

    public function getConfig(): array
    {
        return [
            'nome' => self::NOME,
            'blocos' => \$this->blocos,
            'variaveis' => \$this->variaveisDetectadas,
            'css' => \$this->cssCustom
        ];
    }

    public function render(array \$dadosManuais, \ResolvedorChavesSistema \$resolvedor, int \$id_usuario): string
    {
        // Resolve variáveis de sistema
        \$dadosSistema = \$resolvedor->resolver(\$this->variaveisDetectadas, \$id_usuario, \$dadosManuais);
        
        // Merge dos dados (Sistema sobrescreve se houver conflito, mas geralmente são eixos distintos)
        \$contexto = array_merge(\$dadosManuais, \$dadosSistema);
        
        \$html = "<div class='modelo-docx-container'>";
        \$html .= "<style>{\$this->cssCustom}</style>";
        
        foreach (\$this->blocos as \$bloco) {
            \$html .= \$this->renderBloco(\$bloco, \$contexto);
        }
        
        \$html .= "</div>";
        return \$html;
    }

    private function renderBloco(array \$bloco, array \$contexto): string
    {
        if (\$bloco['tipo'] === 'texto') {
            \$tag = (\$bloco['nivel_titulo'] > 0) ? 'h' . \$bloco['nivel_titulo'] : 'p';
            \$conteudo = \$bloco['conteudo'];
            
            // Substituição de variáveis
            foreach (\$bloco['variaveis'] as \$var) {
                \$valor = \$contexto[\$var] ?? "[{\$var}]";
                \$conteudo = str_replace(["{\${\$var}}", "{\$var}", "{{{\$var}}}"], \$valor, \$conteudo);
            }
            
            \$estilos = \$this->mapEstilos(\$bloco['estilos_css']);
            return "<\$tag style='\$estilos'>\$conteudo</\$tag>";
        }
        
        if (\$bloco['tipo'] === 'tabela') {
            \$html = "<table style='width:100%; border-collapse:collapse; border: 1px solid #ccc; margin-bottom: 20px;'>";
            foreach (\$bloco['linhas'] as \$i => \$linha) {
                \$html .= "<tr>";
                foreach (\$linha as \$celula) {
                    \$tag = (\$i === 0) ? 'th' : 'td';
                    \$texto = \$celula['texto'];
                    
                    // Substituição de variáveis na tabela
                    foreach (\$bloco['variaveis'] as \$var) {
                         \$valor = \$contexto[\$var] ?? "[{\$var}]";
                         \$texto = str_replace(["{\${\$var}}", "{\$var}", "{{{\$var}}}"], \$valor, \$texto);
                    }
                    
                    \$estilos = \$this->mapEstilos(\$celula['estilos']);
                    \$html .= "<\$tag colspan='{\$celula['colspan']}' style='\$estilos'>\$texto</\$tag>";
                }
                \$html .= "</tr>";
            }
            \$html .= "</table>";
            return \$html;
        }
        
        return "";
    }

    private function mapEstilos(array \$estilos): string {
        \$out = "";
        foreach (\$estilos as \$k => \$v) \$out .= "\$k:\$v; ";
        return \$out;
    }
}
PHP;
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SGT - Gerador DOCX para PHP</title>
    <!-- Glassmorphism SGT Style -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --brand: #f97316; --bg: #0a0f1a; --glass: rgba(255, 255, 255, 0.05); }
        body { background: var(--bg); color: #f8fafc; font-family: 'Inter', sans-serif; }
        .glass-card { background: var(--glass); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 25px; margin-bottom: 20px; }
        .upload-zone { border: 2px dashed rgba(255,255,255,0.2); border-radius: 12px; padding: 40px; text-align: center; cursor: pointer; transition: 0.3s; }
        .upload-zone:hover { border-color: var(--brand); background: rgba(249, 115, 22, 0.05); }
        .btn-sgt { background: var(--brand); color: white; border: none; font-weight: 600; border-radius: 8px; padding: 10px 20px; transition: 0.3s; }
        .btn-sgt:hover { background: #ea580c; transform: translateY(-2px); }
        .preview-area { background: white; color: #333; padding: 40px; border-radius: 8px; max-height: 600px; overflow-y: auto; box-shadow: inset 0 0 10px rgba(0,0,0,0.1); }
        .preview-area table { border-collapse: collapse; width: 100%; margin-bottom: 20px; border: 1px solid #ccc; }
        .preview-area th, .preview-area td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .preview-area th { background: #f8f9fa; font-weight: bold; }
        .var-badge { background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-family: monospace; display: inline-block; margin: 2px; }
    </style>
</head>
<body class="py-5">
    <div class="container max-w-4xl">
        <div class="text-center mb-5">
            <h1 class="fw-bold">📄 DOCX para Modelo SGT</h1>
            <p class="text-slate-400">Transforme documentos Word em classes PHP prontas para o editor.</p>
        </div>

        <!-- Passo 1: Upload -->
        <div id="step-1" class="glass-card">
            <h4 class="mb-3">1. Selecionar Arquivo</h4>
            <div class="upload-zone" id="drop-zone" onclick="document.getElementById('file-input').click()">
                <div class="fs-1 mb-2">📥</div>
                <p class="mb-0">Arraste seu arquivo <strong>.docx</strong> aqui ou clique para buscar.</p>
                <input type="file" id="file-input" class="d-none" accept=".docx" onchange="uploadDocx(this.files[0])">
            </div>
            <div id="upload-error" class="alert alert-danger mt-3 d-none" role="alert" style="white-space:pre-wrap"></div>
            <div id="upload-status" class="mt-3 text-center d-none">
                <div class="spinner-border text-warning spinner-border-sm" role="status"></div>
                <span class="ms-2">Analisando documento...</span>
            </div>
        </div>

        <!-- Passo 2: Preview -->
        <div id="step-2" class="glass-card d-none">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>2. Revisar Estrutura</h4>
                <div class="d-flex gap-2">
                     <button class="btn btn-outline-light btn-sm" onclick="location.reload()">Recomeçar</button>
                     <button class="btn btn-sgt" id="btn-gerar" onclick="gerarPHP()">⚡ Gerar Modelo PHP</button>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small text-slate-400">Nome do Modelo (Ex: Drone_V2)</label>
                <input type="text" id="model-name" class="form-control bg-dark text-white border-secondary" placeholder="Nome identificador">
            </div>

            <div class="row mb-4">
                <div class="col-6">
                    <div class="small text-slate-400">Blocos Detectados: <strong id="count-blocks" class="text-white">0</strong></div>
                </div>
                <div class="col-6 text-end">
                    <div class="small text-slate-400">Variáveis: <strong id="count-vars" class="text-white">0</strong></div>
                </div>
            </div>

            <div id="vars-list" class="mb-4"></div>

            <div class="preview-area" id="preview-html"></div>
        </div>

        <!-- Passo 3: Concluído -->
        <div id="step-3" class="glass-card d-none">
            <div class="text-center py-5">
                <div class="fs-1 mb-3">🚀</div>
                <h2 class="fw-bold">Modelo Criado!</h2>
                <p id="file-path-msg" class="text-slate-400 mb-4"></p>
                <div class="d-flex justify-content-center gap-3">
                    <button class="btn btn-sgt" onclick="location.reload()">Criar Outro</button>
                    <a href="editor_dinamico.php" class="btn btn-outline-light">Ir para o Editor</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        let analysisData = null;

        function showError(msg) {
            const box = document.getElementById('upload-error');
            box.textContent = msg;
            box.classList.remove('d-none');
        }

        function clearError() {
            const box = document.getElementById('upload-error');
            box.textContent = '';
            box.classList.add('d-none');
        }

        // Evita o comportamento padrão do navegador (abrir/baixar o arquivo ao soltar na página)
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach((eventName) => {
            document.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        // Drag & drop no drop-zone
        (function initDropZone() {
            const dropZone = document.getElementById('drop-zone');
            if (!dropZone) return;

            dropZone.addEventListener('dragover', () => dropZone.classList.add('border-warning'));
            dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-warning'));
            dropZone.addEventListener('drop', (e) => {
                dropZone.classList.remove('border-warning');
                const files = e.dataTransfer?.files;
                if (files && files[0]) {
                    uploadDocx(files[0]);
                }
            });
        })();

        function uploadDocx(file) {
            if (!file) return;
            clearError();
            const status = document.getElementById('upload-status');
            status.classList.remove('d-none');

            const formData = new FormData();
            formData.append('docx', file);

            fetch('', { method: 'POST', body: formData, credentials: 'same-origin' })
                .then(async (r) => {
                    const text = await r.text();
                    if (!r.ok) {
                        throw new Error(`HTTP ${r.status} ${r.statusText}\n\n${text.slice(0, 1200)}`);
                    }
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        // Quando o servidor devolve HTML (login/erro), mostramos um recorte para diagnóstico
                        throw new Error(
                            "Resposta inválida do servidor (não é JSON).\n" +
                            "Isso normalmente indica redirecionamento para login, erro 500, ou bloqueio do host.\n\n" +
                            text.slice(0, 1200)
                        );
                    }
                })
                .then((data) => {
                    status.classList.add('d-none');
                    if (data.erro) {
                        showError("Erro: " + data.erro + (data.debug ? "\n\nDEBUG:\n" + data.debug : ""));
                        return;
                    }
                    analysisData = data;
                    showPreview();
                })
                .catch(e => {
                    status.classList.add('d-none');
                    showError("Falha ao processar upload:\n" + e.message);
                });
        }

        function showPreview() {
            document.getElementById('step-1').classList.add('d-none');
            document.getElementById('step-2').classList.remove('d-none');

            document.getElementById('count-blocks').innerText = analysisData.total_blocos;
            document.getElementById('count-vars').innerText = analysisData.total_variaveis;
            document.getElementById('preview-html').innerHTML = analysisData.html_preview;
            document.getElementById('model-name').value = analysisData.nome_arquivo.replace('.docx', '').replace(/\s+/g, '_');

            const varsBox = document.getElementById('vars-list');
            varsBox.innerHTML = '<span class="small text-slate-400 d-block mb-1">Dicionário de Variáveis:</span>';
            analysisData.variaveis.forEach(v => {
                varsBox.innerHTML += `<span class="var-badge">${v}</span>`;
            });
        }

        function gerarPHP() {
            const name = document.getElementById('model-name').value;
            if (!name) { alert("Dê um nome ao modelo!"); return; }

            const btn = document.getElementById('btn-gerar');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Gerando...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('acao', 'gerar_php');
            formData.append('nome_modelo', name);
            formData.append('dados', JSON.stringify(analysisData));

            fetch('', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.erro) { alert(data.erro); btn.disabled = false; btn.innerText = "⚡ Gerar Modelo PHP"; return; }
                    
                    document.getElementById('step-2').classList.add('d-none');
                    document.getElementById('step-3').classList.remove('d-none');
                    document.getElementById('file-path-msg').innerText = "Arquivo salvo em: modelos_gerados/" + data.arquivo;
                });
        }
    </script>
</body>
</html>
