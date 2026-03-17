<?php
/**
 * gerador_upload_docx.php 
 * Versão Ultra-Compatível SGT v3.2 - Gera modelos com sintaxe {{variavel}}
 * V3.2 - Correções: Nome único, Editor Inline, Sobrescrita controlada, Sintaxe moderna
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

// 🔥 NOVO: Diretório de saída no SistemaSaaS (sincronização espelhada)
define('MODELOS_DIR_SAAS', 'c:/xampp/htdocs/SistemaSaaS/modelos_gerados/');
define('MODELOS_DIR_LOCAL', __DIR__ . '/modelos_gerados/');

foreach (array(UPLOAD_DIR, MODELOS_DIR_LOCAL) as $dir) {
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
}

// Garante que diretório SaaS existe
if (!is_dir(MODELOS_DIR_SAAS)) @mkdir(MODELOS_DIR_SAAS, 0755, true);

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

// NOVO: Salvar edições do editor inline
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'atualizar_modelo') {
    header('Content-Type: application/json');
    $nomeModelo = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['nome_modelo']);
    $caminhoFinal = MODELOS_DIR_SAAS . 'Modelo' . $nomeModelo . '.php';
    
    if (file_exists($caminhoFinal)) {
        $dados = json_decode($_POST['dados'], true);
        // 🔥 NOVO: Converte para sintaxe moderna antes de gerar
        $dados = converterSintaxeParaModerna($dados);
        $novoCodigo = gerarCodigoPHP($dados, $nomeModelo);
        
        if (file_put_contents($caminhoFinal, $novoCodigo)) {
            // Sincroniza também no local (backup)
            @file_put_contents(MODELOS_DIR_LOCAL . 'Modelo' . $nomeModelo . '.php', $novoCodigo);
            
            echo json_encode(array('sucesso' => true, 'mensagem' => 'Modelo atualizado com sintaxe moderna'));
        } else {
            echo json_encode(array('erro' => 'Erro ao atualizar arquivo'));
        }
    } else {
        echo json_encode(array('erro' => 'Modelo não encontrado'));
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'gerar_php') {
    header('Content-Type: application/json');
    try {
        $dados = json_decode($_POST['dados'], true);
        if (!$dados || !isset($dados['blocos'])) {
            echo json_encode(array('erro' => 'Dados inválidos ou corrompidos. Tente re-fazer o upload.'));
            exit;
        }
        
        // 🔥 NOVO: Converte sintaxe legacy para moderna
        $dados = converterSintaxeParaModerna($dados);
        
        $nomeBase = $_POST['nome_modelo'];
        $nomeBase = preg_replace('/^[a-f0-9]{13}_/', '', $nomeBase);
        $nomeModelo = preg_replace('/[^a-zA-Z0-9]/', '', $nomeBase);
        
        if (empty($nomeModelo)) {
            echo json_encode(array('erro' => 'Nome inválido'));
            exit;
        }
        
        $codigo = gerarCodigoPHP($dados, $nomeModelo);
        
        // Salva DIRETAMENTE no SistemaSaaS (diretório espelho)
        $caminhoFinal = MODELOS_DIR_SAAS . 'Modelo' . $nomeModelo . '.php';
        
        // Backup se existir
        if (file_exists($caminhoFinal)) {
            $backup = $caminhoFinal . '.backup_' . date('YmdHis');
            @copy($caminhoFinal, $backup);
            limparBackupsAntigos(MODELOS_DIR_SAAS, 'Modelo' . $nomeModelo . '.php.backup_', 3);
        }
        
        $salvo = file_put_contents($caminhoFinal, $codigo);
        
        // Cópia local opcional (para registro)
        @file_put_contents(MODELOS_DIR_LOCAL . 'Modelo' . $nomeModelo . '.php', $codigo);

        if ($salvo) {
            echo json_encode(array(
                'sucesso' => true, 
                'arquivo' => 'Modelo' . $nomeModelo . '.php',
                'nome_limpo' => $nomeModelo,
                'sobrescrito' => file_exists($caminhoFinal . '.backup_' . date('YmdHis')) ? true : false,
                'mensagem' => 'Modelo gerado com sintaxe {{variavel}} moderna'
            ));
        } else {
            echo json_encode(array('erro' => 'Erro ao salvar arquivo no SistemaSaaS. Verifique permissões.'));
        }
    } catch (Throwable $e) {
        echo json_encode(array('erro' => 'Exceção PHP: ' . $e->getMessage()));
    }
    exit;
}

// Função auxiliar para limpar backups antigos
function limparBackupsAntigos($diretorio, $prefixo, $manter = 3) {
    $backups = glob($diretorio . $prefixo . '*');
    if (count($backups) > $manter) {
        usort($backups, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        $paraRemover = array_slice($backups, 0, count($backups) - $manter);
        foreach ($paraRemover as $file) {
            @unlink($file);
        }
    }
}

/**
 * Remove vírgulas trailing de arrays PHP exportados (compatibilidade PHP < 7.3)
 */
function removeTrailingComma($str) {
    return preg_replace('/,([\s\)]*\))/', '$1', $str);
}

/**
 * 🔥 NOVO: Converte sintaxe ${variavel} para {{variavel}}
 */
function converterSintaxeParaModerna(array $dados): array {
    if (isset($dados['blocos']) && is_array($dados['blocos'])) {
        foreach ($dados['blocos'] as &$bloco) {
            if (isset($bloco['conteudo'])) {
                $bloco['conteudo'] = converterVariaveisLegacy($bloco['conteudo']);
            }
            if (isset($bloco['conteudo_html'])) {
                $bloco['conteudo_html'] = converterVariaveisLegacy($bloco['conteudo_html']);
            }
            // Converte também nas linhas de tabela
            if (isset($bloco['linhas']) && is_array($bloco['linhas'])) {
                foreach ($bloco['linhas'] as &$linha) {
                    foreach ($linha as &$celula) {
                        if (is_array($celula) && isset($celula['texto'])) {
                            $celula['texto'] = converterVariaveisLegacy($celula['texto']);
                        } elseif (is_string($celula)) {
                            $celula = converterVariaveisLegacy($celula);
                        }
                    }
                }
            }
        }
    }
    
    // Também converte a lista de variáveis detectadas para referência
    if (isset($dados['variaveis']) && is_array($dados['variaveis'])) {
        $dados['variaveis_modernas'] = array_map(function($v) {
            return '{{' . $v . '}}';
        }, $dados['variaveis']);
    }
    
    return $dados;
}

/**
 * 🔥 NOVO: Converte ${variavel} → {{variavel}}
 */
function converterVariaveisLegacy(string $texto): string {
    // Evita converter se já estiver no formato moderno misturado
    if (strpos($texto, '{{') !== false && strpos($texto, '${') === false) {
        return $texto; // Já está moderno
    }
    
    // Converte ${variavel} para {{variavel}}
    // Preserva escaping: \${variavel} não converte
    return preg_replace('/(?<!\\\\)\$\{(\w+)\}/', '{{$1}}', $texto);
}

/**
 * Converte blocos do formato DOCX Parser → formato ModeloBase v3.2 (Sintaxe Moderna)
 */
function converterBlocosParaModeloBase(array $blocos): array {
    $resultado = array();
    foreach ($blocos as $bloco) {
        if ($bloco['tipo'] === 'texto') {
            $nivel    = intval($bloco['nivel_titulo'] ?? 0);
            
            // 🔥 NOVO: Já converte para sintaxe moderna aqui
            $conteudo = converterVariaveisLegacy($bloco['conteudo'] ?? '');
            $conteudoHtml = isset($bloco['conteudo_html']) ? converterVariaveisLegacy($bloco['conteudo_html']) : null;

            if ($nivel > 0) {
                $resultado[] = array('tipo' => 'titulo', 'conteudo' => $conteudo, 'nivel' => $nivel);
            } else {
                if ($conteudoHtml !== null) {
                    $resultado[] = array('tipo' => 'texto', 'conteudo' => $conteudoHtml, 'estilo' => 'normal');
                } else {
                    $negritoTotal = (!empty($bloco['estilos_css']['font-weight']) && $bloco['estilos_css']['font-weight'] === 'bold');
                    $estilo = $negritoTotal ? 'destaque' : 'normal';

                    if (isset($bloco['subtipo']) && $bloco['subtipo'] === 'header_footer') {
                        $estilo = 'header_footer';
                    }

                    $resultado[] = array('tipo' => 'texto', 'conteudo' => $conteudo, 'estilo' => $estilo);
                }
            }
        } elseif ($bloco['tipo'] === 'tabela') {
            $linhas = array();
            foreach ($bloco['linhas'] as $linha) {
                $linhaSimples = array();
                foreach ($linha as $celula) {
                    $texto = $celula['texto'] ?? '';
                    // 🔥 NOVO: Converte variáveis nas células
                    $texto = converterVariaveisLegacy($texto);
                    $linhaSimples[] = $texto;
                }
                $linhas[] = $linhaSimples;
            }
            $resultado[] = array('tipo' => 'tabela', 'linhas' => $linhas);
        }
    }
    return $resultado;
}

function gerarCodigoPHP($dados, $nomeClasseSuffix) {
    $nomeClasse = "Modelo" . $nomeClasseSuffix;
    $data = date('d/m/Y H:i');

    // Converte blocos DOCX → formato ModeloBase v3.2 (já com sintaxe moderna)
    $blocosConvertidos = converterBlocosParaModeloBase($dados['blocos']);
    $blocosStr = removeTrailingComma(var_export($blocosConvertidos, true));

    // 🔥 NOVO: Template v3.2 com comentário sobre sintaxe
    $template = <<<'CODE'
<?php
/**
 * MODELO GERADO - SGT Template Engine v3.2
 * Fonte: {{NOME_ARQUIVO}} | Gerado em: {{DATA}}
 * Sintaxe: {{variavel}} (moderna) - compatível com ${variavel} (legacy)
 */

require_once __DIR__ . '/../core/ModeloBase.php';

class {{NOME_CLASSE}} extends ModeloBase
{
    public function getNome(): string
    {
        return '{{NOME_SUFFIX}}';
    }

    protected function definirBlocos(): array
    {
        return {{BLOCOS}};
    }
}
CODE;

    $out = str_replace(
        array('{{NOME_ARQUIVO}}', '{{DATA}}', '{{NOME_CLASSE}}', '{{NOME_SUFFIX}}', '{{BLOCOS}}'),
        array($dados['nome_arquivo'], $data, $nomeClasse, $nomeClasseSuffix, $blocosStr),
        $template
    );
    return $out;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SGT - Gerador DOCX v3.2 (Sintaxe Moderna)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --brand: #f97316; --bg: #0a0f1a; --glass: rgba(255, 255, 255, 0.05); }
        body { background: var(--bg); color: #f8fafc; font-family: sans-serif; }
        .glass-card { background: var(--glass); border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 25px; margin-bottom: 20px; }
        .upload-zone { border: 2px dashed rgba(255,255,255,0.2); border-radius: 12px; padding: 40px; text-align: center; cursor: pointer; transition: all 0.3s; }
        .upload-zone:hover { border-color: var(--brand); background: rgba(249, 115, 22, 0.1); }
        .btn-sgt { background: var(--brand); color: white; border: none; font-weight: 600; padding: 10px 20px; border-radius: 8px; }
        .btn-sgt:hover { background: #ea580c; color: white; }
        .preview-area { background: white; color: #333; padding: 30px; border-radius: 8px; max-height: 500px; overflow-y: auto; }
        
        /* 🔥 NOVO: Badge de sintaxe moderna */
        .badge-moderna { background: #10b981; color: white; font-size: 10px; padding: 2px 8px; border-radius: 4px; margin-left: 8px; }
        
        /* 🔥 NOVO: Estilo para variáveis modernas no preview */
        .var-badge-modern { background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 4px; font-size: 11px; margin: 2px; display: inline-block; cursor: pointer; font-family: monospace; }
        .var-badge-modern:hover { background: #a7f3d0; }
        .var-badge-legacy { background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 4px; font-size: 11px; margin: 2px; display: inline-block; text-decoration: line-through; opacity: 0.6; }
        
        .editor-inline { background: #1e293b; border-radius: 8px; padding: 15px; margin-top: 15px; }
        .editor-bloco { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; padding: 10px; margin-bottom: 10px; }
        .editor-bloco textarea { width: 100%; background: #0f172a; color: #e2e8f0; border: 1px solid #334155; border-radius: 4px; padding: 8px; font-family: monospace; font-size: 13px; resize: vertical; }
        .editor-bloco label { font-size: 11px; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px; display: block; }
        .btn-secundario { background: #334155; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-size: 13px; margin-right: 8px; }
        .btn-secundario:hover { background: #475569; color: white; }
        .aviso-sobrescrita { background: #fef3c7; color: #92400e; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; display: none; }

    </style>
</head>
<body class="py-5">
    <div class="container" style="max-width: 900px;">
        <div class="text-center mb-5">
            <h1>📄 Gerador DOCX v3.2 <span class="badge-moderna">Sintaxe Moderna {{}}</span></h1>
            <p class="text-muted">Upload → Converte ${var} para {{var}} → Salva no SistemaSaaS</p>
        </div>
        
        <!-- PASSO 1: Upload -->
        <div id="step-1" class="glass-card">
            <div class="upload-zone" onclick="document.getElementById('file-input').click()">
                <div class="fs-1">📥</div>
                <p>Arraste seu .docx ou clique para selecionar</p>
                <small class="text-muted">O sistema converterá automaticamente ${variavel} para {{variavel}}</small>
                <input type="file" id="file-input" class="d-none" accept=".docx" onchange="uploadDocx(this.files[0])">
            </div>
            <div id="upload-error" class="alert alert-danger mt-3 d-none"></div>
            <div id="upload-status" class="mt-3 text-center d-none">
                <div class="spinner-border text-warning spinner-border-sm"></div> Analisando e convertendo sintaxe...
            </div>
        </div>

        <!-- PASSO 2: Revisão e Edição -->
        <div id="step-2" class="glass-card d-none">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4>📝 Revisar e Ajustar</h4>
                    <small class="text-muted">Verifique a conversão de variáveis abaixo</small>
                </div>
                <div>
                    <button class="btn btn-secundario" onclick="voltarUpload()">← Voltar</button>
                    <button class="btn btn-sgt" onclick="gerarPHP()">💾 Salvar no SistemaSaaS</button>
                </div>
            </div>

            <div id="aviso-sobrescrita" class="aviso-sobrescrita">
                ⚠️ <strong>Atenção:</strong> Já existe um modelo com este nome. 
                O arquivo será substituído (backup automático criado).
            </div>

            <div class="mb-3">
                <label class="form-label text-light">Nome do Modelo</label>
                <input type="text" id="model-name" class="form-control bg-dark text-white border-secondary" 
                       placeholder="Ex: PropostaDrone" onchange="verificarExistencia()">
                <small class="text-muted">Use apenas letras e números. Prefixo "Modelo" automático.</small>
            </div>

            <div class="mb-3">
                <label class="form-label text-light">
                    Variáveis Detectadas 
                    <span class="badge-moderna">{{moderno}}</span>
                </label>
                <div id="vars-list" class="p-2 bg-dark rounded"></div>
                <small class="text-muted">Clique para copiar no formato {{variavel}}</small>
            </div>

            <!-- Editor Inline de Blocos -->
            <div class="editor-inline">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <label class="mb-0">✏️ Editor de Blocos (Preview da conversão)</label>
                    <small class="text-muted">Sintaxe {{variavel}} aplicada automaticamente</small>
                </div>
                <div id="editor-blocos-container"></div>
            </div>

            <div class="mt-3">
                <label class="form-label text-light">Preview HTML</label>
                <div class="preview-area" id="preview-html"></div>
            </div>
        </div>

        <!-- PASSO 3: Sucesso -->
        <div id="step-3" class="glass-card d-none text-center py-5">
            <h2>🚀 Modelo Salvo no SistemaSaaS!</h2>
            <p id="file-path-msg" class="text-muted mb-4"></p>
            <div class="d-flex justify-content-center gap-3">
                <a id="btn-visualizar-modelo" href="#" target="_blank" class="btn btn-info fw-bold text-white">👀 Ver no SistemaSaaS</a>
                <button class="btn btn-secundario" onclick="novoUpload()">Novo Upload</button>
            </div>
        </div>
    </div>

    <script>
        let analysisData = null;
        let blocosEditados = [];
        let corSelecionada = 'verde';

        function uploadDocx(file) {
            if(!file) return;
            document.getElementById('upload-status').classList.remove('d-none');
            const fd = new FormData(); 
            fd.append('docx', file);
            
            fetch('', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                document.getElementById('upload-status').classList.add('d-none');
                if(data.erro) { 
                    alert(data.erro); 
                    return; 
                }
                
                analysisData = data;
                
                // 🔥 NOVO: Converte variáveis para exibição moderna
                analysisData.variaveis_modernas = analysisData.variaveis.map(v => '{{' + v + '}}');
                
                let nomeLimpo = data.nome_arquivo.replace('.docx', '');
                nomeLimpo = nomeLimpo.replace(/^[a-f0-9]{13}_/, '');
                
                document.getElementById('step-1').classList.add('d-none');
                document.getElementById('step-2').classList.remove('d-none');
                
                document.getElementById('model-name').value = nomeLimpo.replace(/[^a-zA-Z0-9]/g, '');
                
                // 🔥 NOVO: Exibe variáveis com badge moderno
                const vBox = document.getElementById('vars-list');
                vBox.innerHTML = '';
                data.variaveis.forEach((v, i) => { 
                    const span = document.createElement('span');
                    span.className = 'var-badge-modern';
                    span.textContent = '{{' + v + '}}';
                    span.onclick = () => {
                        navigator.clipboard.writeText('{{' + v + '}}');
                        span.style.background = '#86efac';
                        setTimeout(() => span.style.background = '#d1fae5', 500);
                    };
                    vBox.appendChild(span);
                });

                inicializarEditorBlocos(data.blocos);
                atualizarPreview();
                verificarExistencia();
            })
            .catch(e => { 
                alert("Erro fatal no upload. Verifique o console."); 
                console.error(e); 
            });
        }

        function inicializarEditorBlocos(blocos) {
            blocosEditados = JSON.parse(JSON.stringify(blocos));
            const container = document.getElementById('editor-blocos-container');
            container.innerHTML = '';
            
            blocosEditados.forEach((bloco, index) => {
                const div = document.createElement('div');
                div.className = 'editor-bloco';
                
                const label = document.createElement('label');
                label.textContent = `Bloco ${index + 1} (${bloco.tipo})`;
                
                const textarea = document.createElement('textarea');
                textarea.rows = 3;
                
                if (bloco.tipo === 'texto') {
                    // 🔥 NOVO: Já exibe no formato moderno
                    textarea.value = bloco.conteudo;
                    textarea.onchange = (e) => {
                        blocosEditados[index].conteudo = e.target.value;
                        atualizarPreview();
                    };
                } else if (bloco.tipo === 'tabela') {
                    textarea.value = JSON.stringify(bloco.linhas, null, 2);
                    textarea.rows = 6;
                    textarea.onchange = (e) => {
                        try {
                            blocosEditados[index].linhas = JSON.parse(e.target.value);
                            atualizarPreview();
                        } catch(err) {
                            alert('JSON inválido na tabela');
                        }
                    };
                }
                
                div.appendChild(label);
                div.appendChild(textarea);
                container.appendChild(div);
            });
        }

        function atualizarPreview() {
            if (!analysisData) return;

            const tema = corSelecionada;
            const p = getCoresJS(tema);

            let cssTema = `
                .modelo-docx { background: white !important; color: #333 !important; font-family: 'Segoe UI', Arial, sans-serif; }
                .modelo-docx h1 { color: ${p.primaria} !important; border-bottom: 2px solid ${p.primaria} !important; text-align: center; padding-bottom: 8px; font-weight: bold !important; font-size: 22px !important; }
                .modelo-docx h2 { color: ${p.primaria} !important; border-left: 5px solid ${p.secundaria} !important; padding-left: 12px; margin-top: 25px; font-weight: bold !important; font-size: 18px !important; }
                .modelo-docx h3 { color: ${p.secundaria} !important; text-transform: uppercase; font-size: 13px !important; font-weight: bold !important; margin-top: 15px; }
                .modelo-docx p { margin-bottom: 8px; line-height: 1.6; font-size: 14px; }
            `;

            let htmlFull = `<style>${cssTema}</style>`;
            htmlFull += "<div class='modelo-docx' style='padding:40px; border:1px solid #ddd; border-radius:8px; box-shadow: 0 0 20px rgba(0,0,0,0.1);'>";

            blocosEditados.forEach(bloco => {
                let estiloStr = "";
                if (bloco.estilos_css) {
                    for (let prop in bloco.estilos_css) {
                        estiloStr += `${prop}:${bloco.estilos_css[prop]}; `;
                    }
                }

                if (bloco.tipo === 'texto') {
                    let tag = 'p';
                    const nivel = parseInt(bloco.nivel_titulo || 0);
                    if (nivel === 1) tag = 'h1';
                    else if (nivel === 2) tag = 'h2';
                    else if (nivel === 3) tag = 'h3';

                    if (bloco.estilos_css && bloco.estilos_css['font-weight'] === 'bold') {
                        estiloStr += "font-weight: bold !important; ";
                    }

                    // 🔥 NOVO: Destaca variáveis {{}} no preview
                    let conteudo = (bloco.conteudo_html || bloco.conteudo || '')
                        .replace(/\{\{(\w+)\}\}/g, '<span style="background:#d1fae5;color:#065f46;padding:0 4px;border-radius:3px;font-family:monospace;">{{$1}}</span>')
                        .replace(/\n/g, '<br>');

                    let classe = (bloco.subtipo === 'header_footer') ? 'sgt-texto-header_footer' : '';
                    
                    htmlFull += `<${tag} class="${classe}" style="${estiloStr}">${conteudo}</${tag}>`;
                } else if (bloco.tipo === 'tabela') {
                    htmlFull += '<div class="sgt-table-wrap">';
                    htmlFull += '<table>';
                    
                    const meta = bloco.linhas_meta || [];
                    
                    bloco.linhas.forEach((linha, i) => {
                        const isH = meta[i] ? meta[i].is_header : false;
                        htmlFull += '<tr>';
                        linha.forEach(celula => {
                            const tag = isH ? 'th' : 'td';
                            let texto = (celula.texto || '').replace(/\n/g, '<br>');
                            // 🔥 Destaca variáveis nas células também
                            texto = texto.replace(/\{\{(\w+)\}\}/g, '<span style="background:#d1fae5;color:#065f46;padding:0 4px;border-radius:3px;font-family:monospace;">{{$1}}</span>');
                            htmlFull += `<${tag}>${texto}</${tag}>`;
                        });
                        htmlFull += '</tr>';
                    });
                    
                    htmlFull += '</table></div>';
                }
            });
            htmlFull += "</div>";
            document.getElementById('preview-html').innerHTML = htmlFull;
        }

        function verificarExistencia() {
            const nome = document.getElementById('model-name').value;
            if (!nome) return;
            
            const aviso = document.getElementById('aviso-sobrescrita');
            if (analysisData && analysisData.nome_arquivo.includes(nome)) {
                aviso.style.display = 'block';
            } else {
                aviso.style.display = 'none';
            }
        }

        function gerarPHP() {
            const name = document.getElementById('model-name').value;
            if (!name || !/^[a-zA-Z0-9]+$/.test(name)) {
                alert('Nome inválido. Use apenas letras e números.');
                return;
            }

            const dadosAtualizados = {
                ...analysisData,
                blocos: blocosEditados,
                nome_arquivo: name + '.docx'
            };

            const fd = new FormData(); 
            fd.append('acao', 'gerar_php'); 
            fd.append('nome_modelo', name); 
            fd.append('dados', JSON.stringify(dadosAtualizados));
            
            fetch('', { method: 'POST', body: fd })
            .then(r => r.text())
            .then(raw => {
                let data;
                try { data = JSON.parse(raw); }
                catch(e) { alert('Resposta inesperada:\n' + raw.substring(0, 300)); return; }

                if(data.erro) { 
                    alert('❌ Erro:\n' + data.erro); 
                    return; 
                }
                
                document.getElementById('step-2').classList.add('d-none');
                document.getElementById('step-3').classList.remove('d-none');
                
                const msg = data.sobrescrito 
                    ? `✅ Modelo substituído: <code>SistemaSaaS/modelos_gerados/${data.arquivo}</code><br><small>Backup criado. ${data.mensagem}</small>`
                    : `✅ Novo modelo salvo: <code>SistemaSaaS/modelos_gerados/${data.arquivo}</code><br><small>${data.mensagem}</small>`;
                
                document.getElementById('file-path-msg').innerHTML = msg;
                document.getElementById('btn-visualizar-modelo').href = 'http://localhost/SistemaSaaS/modules/producao/visualizar_modelo_demo.php?modelo=' + data.nome_limpo;
            })
            .catch(e => alert('Erro de rede: ' + e));
        }

        function voltarUpload() {
            document.getElementById('step-2').classList.add('d-none');
            document.getElementById('step-1').classList.remove('d-none');
            document.getElementById('file-input').value = '';
        }

        function novoUpload() {
            location.reload();
        }

        function getCoresJS(tema) {
            const paletas = {
                verde:   { primaria: '#065f46', secundaria: '#10b981', fundo: '#ecfdf5', texto: '#047857' },
                azul:    { primaria: '#1e3a8a', secundaria: '#3b82f6', fundo: '#eff6ff', texto: '#1e40af' },
                laranja: { primaria: '#7c2d12', secundaria: '#f59e0b', fundo: '#fffbeb', texto: '#92400e' },
                cinza:   { primaria: '#1f2937', secundaria: '#6b7280', fundo: '#f9fafb', texto: '#374151' }
            };
            return paletas[tema] || paletas.verde;
        }
    </script>
</body>
</html>