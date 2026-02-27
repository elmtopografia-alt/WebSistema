<?php
/**
 * gerador_upload_docx.php 
 * Versão Ultra-Compatível SGT (Resolve erros de sintaxe em XAMPP antigo)
 * V2.0 - Correções: Nome único, Editor Inline, Sobrescrita controlada
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
define('MODELOS_DIR', __DIR__ . '/modelos_prod/');

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

// NOVO: Salvar edições do editor inline
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'atualizar_modelo') {
    header('Content-Type: application/json');
    $nomeModelo = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['nome_modelo']);
    $caminhoFinal = MODELOS_DIR . 'Modelo' . $nomeModelo . '.php';
    
    if (file_exists($caminhoFinal)) {
        // Atualiza apenas os dados, mantendo a estrutura PHP
        $dados = json_decode($_POST['dados'], true);
        $novoCodigo = gerarCodigoPHP($dados, $nomeModelo);
        
        if (file_put_contents($caminhoFinal, $novoCodigo)) {
            echo json_encode(array('sucesso' => true, 'mensagem' => 'Modelo atualizado'));
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
    
    // CORREÇÃO CASO 1: Extrair nome base sem o hash do uniqid
    $nomeBase = $_POST['nome_modelo'];
    
    // Remove hash do uniqid (padrão: 13 caracteres hex + underscore no início)
    $nomeBase = preg_replace('/^[a-f0-9]{13}_/', '', $nomeBase);
    
    // Limpa caracteres especiais mantendo apenas letras e números
    $nomeModelo = preg_replace('/[^a-zA-Z0-9]/', '', $nomeBase);
    
    if (empty($nomeModelo)) {
        echo json_encode(array('erro' => 'Nome inválido'));
        exit;
    }
    
    // CORREÇÃO CASO 1: Sobrescreve arquivo existente (não cria duplicatas)
    $corPadrao = isset($_POST['cor_padrao']) ? $_POST['cor_padrao'] : 'verde';
    $codigo = gerarCodigoPHP($dados, $nomeModelo, $corPadrao);
    $caminhoFinal = MODELOS_DIR . 'Modelo' . $nomeModelo . '.php';
    
    // Se arquivo já existe, faz backup temporário (opcional, pode remover)
    if (file_exists($caminhoFinal)) {
        $backup = $caminhoFinal . '.backup_' . date('YmdHis');
        @copy($caminhoFinal, $backup);
        // Mantém apenas os últimos 3 backups
        limparBackupsAntigos(MODELOS_DIR, 'Modelo' . $nomeModelo . '.php.backup_', 3);
    }
    
    // Configurações de sincronização remota
    $urlSincronizacao = 'https://elmtopografia.com.br/Orcamento/recebedor_modelos.php';
    $chaveSincronizacao = 'SGT_DOCX_SYNC_77A9B2C3X';
    $msgSync = '';

    if (file_put_contents($caminhoFinal, $codigo)) {
        
        // --- INÍCIO: ENVIAR PARA SERVIDOR WEB (FTP AUTOMÁTICO VIA POST) ---
        if (function_exists('curl_init') && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
            $ch = curl_init();
            $cfile = new CURLFile($caminhoFinal, 'application/x-httpd-php', 'Modelo' . $nomeModelo . '.php');
            
            $post = [
                'chave' => $chaveSincronizacao,
                'modelo_php' => $cfile
            ];
            
            curl_setopt($ch, CURLOPT_URL, $urlSincronizacao);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15); // timeout de 15 segundos
            
            $respostaRaw = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpcode == 200) {
                $msgSync = ' <span style="color:#10b981; font-weight:bold;">(Sincronizado automaticamente com a Web!)</span>';
            } else {
                 $msgSync = ' <span style="color:#ef4444; font-weight:bold;">(Erro ao sincronizar online: ' . htmlspecialchars($respostaRaw ?? '') . ')</span>';
            }
        }
        // --- FIM: ENVIAR PARA SERVIDOR WEB ---

        echo json_encode(array(
            'sucesso' => true, 
            'arquivo' => 'Modelo' . $nomeModelo . '.php',
            'nome_limpo' => $nomeModelo,
            'sobrescrito' => file_exists($caminhoFinal . '.backup_' . date('YmdHis')) ? true : false,
            'mensagem_sync' => $msgSync
        ));
    } else {
        echo json_encode(array('erro' => 'Erro ao salvar arquivo. Verifique permissões da pasta modelos_prod/'));
    }
    } catch (Throwable $e) {
        echo json_encode(array('erro' => 'Exceção PHP: ' . $e->getMessage() . ' em ' . basename($e->getFile()) . ':' . $e->getLine()));
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
        // Remove os mais antigos
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
 * Converte blocos do formato DOCX Parser → formato ModeloBase v2
 */
function converterBlocosParaModeloBase(array $blocos): array {
    $resultado = array();
    foreach ($blocos as $bloco) {
        if ($bloco['tipo'] === 'texto') {
            $nivel    = intval($bloco['nivel_titulo'] ?? 0);
            $conteudo = $bloco['conteudo'] ?? '';
            if ($nivel > 0) {
                $resultado[] = array('tipo' => 'titulo', 'conteudo' => $conteudo, 'nivel' => $nivel);
            } else {
                $estilo = (!empty($bloco['estilos_css']['font-weight']) && $bloco['estilos_css']['font-weight'] === 'bold')
                    ? 'destaque'
                    : 'normal';
                $resultado[] = array('tipo' => 'texto', 'conteudo' => $conteudo, 'estilo' => $estilo);
            }
        } elseif ($bloco['tipo'] === 'tabela') {
            $linhas = array();
            foreach ($bloco['linhas'] as $linha) {
                $linhaSimples = array();
                foreach ($linha as $celula) {
                    $linhaSimples[] = $celula['texto'] ?? '';
                }
                $linhas[] = $linhaSimples;
            }
            $resultado[] = array('tipo' => 'tabela', 'linhas' => $linhas);
        }
    }
    return $resultado;
}

function gerarCodigoPHP($dados, $nomeClasseSuffix, $corPadrao = 'verde') {
    $nomeClasse = "Modelo" . $nomeClasseSuffix;
    $data = date('d/m/Y H:i');

    // Converte blocos DOCX → formato ModeloBase v2
    $blocosConvertidos = converterBlocosParaModeloBase($dados['blocos']);
    $blocosStr = removeTrailingComma(var_export($blocosConvertidos, true));

    // NOTA: CODE; deve estar na COLUNA 0 para compatibilidade com PHP < 7.3
    $template = <<<'CODE'
<?php
/**
 * MODELO GERADO - SGT Template Engine v2
 * Fonte: {{NOME_ARQUIVO}} | Gerado em: {{DATA}}
 */

require_once __DIR__ . '/../core/ModeloBase.php';

class {{NOME_CLASSE}} extends ModeloBase
{
    const COR_PADRAO = '{{COR_PADRAO}}';

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
        array('{{NOME_ARQUIVO}}', '{{DATA}}', '{{NOME_CLASSE}}', '{{NOME_SUFFIX}}', '{{BLOCOS}}', '{{COR_PADRAO}}'),
        array($dados['nome_arquivo'], $data, $nomeClasse, $nomeClasseSuffix, $blocosStr, $corPadrao),
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
        .upload-zone { border: 2px dashed rgba(255,255,255,0.2); border-radius: 12px; padding: 40px; text-align: center; cursor: pointer; transition: all 0.3s; }
        .upload-zone:hover { border-color: var(--brand); background: rgba(249, 115, 22, 0.1); }
        .btn-sgt { background: var(--brand); color: white; border: none; font-weight: 600; padding: 10px 20px; border-radius: 8px; }
        .btn-sgt:hover { background: #ea580c; color: white; }
        .preview-area { background: white; color: #333; padding: 30px; border-radius: 8px; max-height: 500px; overflow-y: auto; }
        .var-badge { background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 11px; margin: 2px; display: inline-block; cursor: pointer; }
        .var-badge:hover { background: #bae6fd; }
        
        /* CORREÇÃO CASO 2: Editor Inline */
        .editor-inline { background: #1e293b; border-radius: 8px; padding: 15px; margin-top: 15px; }
        .editor-bloco { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; padding: 10px; margin-bottom: 10px; }
        .editor-bloco textarea { width: 100%; background: #0f172a; color: #e2e8f0; border: 1px solid #334155; border-radius: 4px; padding: 8px; font-family: monospace; font-size: 13px; resize: vertical; }
        .editor-bloco label { font-size: 11px; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px; display: block; }
        .btn-secundario { background: #334155; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-size: 13px; margin-right: 8px; }
        .btn-secundario:hover { background: #475569; color: white; }
        .aviso-sobrescrita { background: #fef3c7; color: #92400e; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; display: none; }
        /* Seletor visual de cor */
        .cor-card { cursor: pointer; }
        .cor-card input { display: none; }
        .cor-card-label { display: inline-block; color: #fff; padding: 10px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; border: 2px solid transparent; transition: all 0.2s; cursor: pointer; }
        .cor-card-label:hover { transform: translateY(-2px); opacity: 0.9; }
        .cor-card input:checked + .cor-card-label { border-color: #fff; box-shadow: 0 0 0 2px rgba(255,255,255,0.4); }
    </style>
</head>
<body class="py-5">
    <div class="container" style="max-width: 900px;">
        <div class="text-center mb-5">
            <h1>📄 Gerador DOCX (Modo Seguro)</h1>
            <p class="text-muted">Upload → Revisar → Salvar (com substituição inteligente)</p>
        </div>
        
        <!-- PASSO 1: Upload -->
        <div id="step-1" class="glass-card">
            <div class="upload-zone" onclick="document.getElementById('file-input').click()">
                <div class="fs-1">📥</div>
                <p>Arraste seu .docx ou clique para selecionar</p>
                <small class="text-muted">O sistema manterá apenas um arquivo por modelo</small>
                <input type="file" id="file-input" class="d-none" accept=".docx" onchange="uploadDocx(this.files[0])">
            </div>
            <div id="upload-error" class="alert alert-danger mt-3 d-none"></div>
            <div id="upload-status" class="mt-3 text-center d-none">
                <div class="spinner-border text-warning spinner-border-sm"></div> Analisando documento...
            </div>
        </div>

        <!-- PASSO 2: Revisão e Edição (CORREÇÃO CASO 2) -->
        <div id="step-2" class="glass-card d-none">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4>📝 Revisar e Ajustar</h4>
                    <small class="text-muted">Correções pontuais antes de salvar</small>
                </div>
                <div>
                    <button class="btn btn-secundario" onclick="voltarUpload()">← Voltar</button>
                    <button class="btn btn-sgt" onclick="gerarPHP()">💾 Salvar Modelo</button>
                </div>
            </div>

            <div id="aviso-sobrescrita" class="aviso-sobrescrita">
                ⚠️ <strong>Atenção:</strong> Já existe um modelo com este nome. 
                O arquivo será substituído (backup automático criado).
            </div>

            <div class="mb-3">
                <label class="form-label text-light">Nome do Modelo (sem hash)</label>
                <input type="text" id="model-name" class="form-control bg-dark text-white border-secondary" 
                       placeholder="Ex: PropostaDrone" onchange="verificarExistencia()">
                <small class="text-muted">Use apenas letras e números. O prefixo "Modelo" será adicionado automaticamente.</small>
            </div>

            <div class="mb-3">
                <label class="form-label text-light">Variáveis Detectadas (clique para copiar)</label>
                <div id="vars-list" class="p-2 bg-dark rounded"></div>
            </div>

            <div class="mb-4">
                <label class="form-label text-light">🎨 Cor Padrão do Modelo</label>
                <div class="d-flex gap-2 flex-wrap mt-2">
                    <label class="cor-card">
                        <input type="radio" name="cor_padrao" value="verde" checked onchange="atualizarPreview()">
                        <span class="cor-card-label" style="background:#065f46;">🌿 Topografia</span>
                    </label>
                    <label class="cor-card">
                        <input type="radio" name="cor_padrao" value="azul" onchange="atualizarPreview()">
                        <span class="cor-card-label" style="background:#1e3a8a;">🏢 Corporativo</span>
                    </label>
                    <label class="cor-card">
                        <input type="radio" name="cor_padrao" value="laranja" onchange="atualizarPreview()">
                        <span class="cor-card-label" style="background:#7c2d12;">⚡ Energia</span>
                    </label>
                    <label class="cor-card">
                        <input type="radio" name="cor_padrao" value="cinza" onchange="atualizarPreview()">
                        <span class="cor-card-label" style="background:#1f2937;">📋 Institucional</span>
                    </label>
                </div>
                <small class="text-muted mt-1 d-block">Cor padrão do modelo gerado — pode ser alterada ao gerar o documento.</small>
            </div>

            <!-- Editor Inline de Blocos -->
            <div class="editor-inline">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <label class="mb-0">✏️ Editor de Blocos</label>
                    <small class="text-muted">Edite o conteúdo antes de salvar</small>
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
            <h2>🚀 Modelo Salvo!</h2>
            <p id="file-path-msg" class="text-muted mb-4"></p>
            <div class="d-flex justify-content-center gap-3">
                <a href="https://elmtopografia.com.br/Orcamento/painel.php" class="btn btn-sgt">Ir para o Painel principal</a>
                <button class="btn btn-secundario" onclick="novoUpload()">Novo Upload</button>
            </div>
        </div>
    </div>

    <script>
        let analysisData = null;
        let blocosEditados = [];

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
                
                // CORREÇÃO CASO 1: Remove hash do nome para exibição
                let nomeLimpo = data.nome_arquivo.replace('.docx', '');
                nomeLimpo = nomeLimpo.replace(/^[a-f0-9]{13}_/, ''); // Remove hash do uniqid
                
                document.getElementById('step-1').classList.add('d-none');
                document.getElementById('step-2').classList.remove('d-none');
                
                // Preenche campos
                document.getElementById('model-name').value = nomeLimpo.replace(/[^a-zA-Z0-9]/g, '');
                
                // Injeta CSS no preview
                const previewHtml = `<style>${data.css_geral}</style><div class="modelo-docx">${data.html_preview}</div>`;
                document.getElementById('preview-html').innerHTML = previewHtml;
                
                // Variáveis
                const vBox = document.getElementById('vars-list');
                vBox.innerHTML = '';
                data.variaveis.forEach(v => { 
                    const span = document.createElement('span');
                    span.className = 'var-badge';
                    span.textContent = v;
                    span.onclick = () => {
                        navigator.clipboard.writeText('{{' + v + '}}');
                        span.style.background = '#86efac';
                        setTimeout(() => span.style.background = '#e0f2fe', 500);
                    };
                    vBox.appendChild(span);
                });

                // CORREÇÃO CASO 2: Inicializa editor inline de blocos
                inicializarEditorBlocos(data.blocos);
                
                // Verifica se arquivo já existe
                verificarExistencia();
            })
            .catch(e => { 
                alert("Erro fatal no upload. Verifique o console."); 
                console.error(e); 
            });
        }

        // CORREÇÃO CASO 2: Editor inline de blocos
        function inicializarEditorBlocos(blocos) {
            blocosEditados = JSON.parse(JSON.stringify(blocos)); // Deep copy
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

            const tema = getCorSelecionada();
            const p    = getCoresJS(tema);

            // CSS espelhando TemaEngine v2 via variáveis CSS
            let cssTema = `
                .modelo-docx { background: white; color: #333; }
                .modelo-docx h1 { color: ${p.primaria} !important; border-bottom: 3px solid ${p.primaria} !important; text-align: center; padding-bottom: 8px; }
                .modelo-docx h2 { color: ${p.texto} !important; border-left: 4px solid ${p.secundaria} !important; padding-left: 10px; margin-top: 18px; }
                .modelo-docx h3 { color: ${p.secundaria} !important; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.04em; }
                .modelo-docx table { border: 1px solid ${p.secundaria} !important; border-collapse: collapse; width: 100%; margin: 15px 0; }
                .modelo-docx table th { background: ${p.primaria}; color: white !important; padding: 8px; }
                .modelo-docx table td { border-bottom: 1px solid #e5e7eb; padding: 8px; }
                .modelo-docx table tr:nth-child(even) { background: ${p.fundo}; }
            `;

            let html = `<style>${cssTema} ${analysisData.css_geral}</style>`;
            html += "<div class='modelo-docx' style='padding:24px;'>";

            blocosEditados.forEach(bloco => {
                if (bloco.tipo === 'texto') {
                    let tag = 'p';
                    if (bloco.nivel_titulo == 1) tag = 'h1';
                    else if (bloco.nivel_titulo == 2) tag = 'h2';
                    else if (bloco.nivel_titulo == 3) tag = 'h3';
                    html += `<${tag}>${bloco.conteudo}</${tag}>`;
                } else if (bloco.tipo === 'tabela') {
                    html += '<table border="1">';
                    bloco.linhas.forEach((linha, i) => {
                        html += '<tr>';
                        linha.forEach(celula => {
                            const tag = i === 0 ? 'th' : 'td';
                            let estilos = "";
                            if (celula.estilos) {
                                for (let k in celula.estilos) estilos += `${k}:${celula.estilos[k]}; `;
                            }
                            html += `<${tag} colspan="${celula.colspan || 1}" style="${estilos}">${celula.texto.replace(/\n/g, '<br>')}</${tag}>`;
                        });
                        html += '</tr>';
                    });
                    html += '</table>';
                }
            });
            html += "</div>";
        }


        function verificarExistencia() {
            // Simulação simples - em produção, fazer requisição AJAX para verificar
            const nome = document.getElementById('model-name').value;
            if (!nome) return;
            
            // Aqui você pode adicionar uma chamada AJAX para verificar se o arquivo existe
            // Por enquanto, vamos assumir que se o usuário está editando, pode existir
            const aviso = document.getElementById('aviso-sobrescrita');
            // Mostra aviso apenas se o nome for similar ao original (indicando re-upload)
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

            // Atualiza dados com edições do usuário
            const dadosAtualizados = {
                ...analysisData,
                blocos: blocosEditados,
                nome_arquivo: name + '.docx'
            };

            const fd = new FormData(); 
            fd.append('acao', 'gerar_php'); 
            fd.append('nome_modelo', name); 
            fd.append('cor_padrao', getCorSelecionada());
            fd.append('dados', JSON.stringify(dadosAtualizados));
            
            fetch('', { method: 'POST', body: fd })
            .then(r => r.text())
            .then(raw => {
                let data;
                try { data = JSON.parse(raw); }
                catch(e) { alert('Resposta inesperada do servidor:\n' + raw.substring(0, 300)); return; }

                if(data.erro) { 
                    alert('❌ Erro ao salvar:\n' + data.erro); 
                    return; 
                }
                
                document.getElementById('step-2').classList.add('d-none');
                document.getElementById('step-3').classList.remove('d-none');
                
                let msgSyncHtml = data.mensagem_sync ? `<br>${data.mensagem_sync}` : '';

                const msg = data.sobrescrito 
                    ? `✅ Modelo substituído: <code>modelos_prod/${data.arquivo}</code><br><small>Backup anterior criado automaticamente</small>${msgSyncHtml}`
                    : `✅ Novo modelo salvo: <code>modelos_prod/${data.arquivo}</code>${msgSyncHtml}`;
                
                document.getElementById('file-path-msg').innerHTML = msg;
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

        function getCorSelecionada() {
            const checked = document.querySelector('input[name="cor_padrao"]:checked');
            return checked ? checked.value : 'verde';
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