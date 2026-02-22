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
    $dados = json_decode($_POST['dados'], true);
    
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
    $codigo = gerarCodigoPHP($dados, $nomeModelo);
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
        echo json_encode(array('erro' => 'Erro ao salvar arquivo'));
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
                document.getElementById('preview-html').innerHTML = data.html_preview;
                
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
            // Atualiza preview com dados editados (simplificado)
            let html = "<div style='padding:20px;'>";
            blocosEditados.forEach(bloco => {
                if (bloco.tipo === 'texto') {
                    html += `<p>${bloco.conteudo}</p>`;
                } else if (bloco.tipo === 'tabela') {
                    html += '<table border="1" style="width:100%;">';
                    bloco.linhas.forEach((linha, i) => {
                        html += '<tr>';
                        linha.forEach(celula => {
                            const tag = i === 0 ? 'th' : 'td';
                            html += `<${tag}>${celula.texto}</${tag}>`;
                        });
                        html += '</tr>';
                    });
                    html += '</table>';
                }
            });
            html += "</div>";
            document.getElementById('preview-html').innerHTML = html;
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
            fd.append('dados', JSON.stringify(dadosAtualizados));
            
            fetch('', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if(data.erro) { 
                    alert(data.erro); 
                    return; 
                }
                
                document.getElementById('step-2').classList.add('d-none');
                document.getElementById('step-3').classList.remove('d-none');
                
                let msgSyncHtml = data.mensagem_sync ? `<br>${data.mensagem_sync}` : '';

                const msg = data.sobrescrito 
                    ? `Modelo substituído: modelos_gerados/${data.arquivo}<br><small>Backup anterior criado automaticamente</small>${msgSyncHtml}`
                    : `Novo modelo salvo: modelos_gerados/${data.arquivo}${msgSyncHtml}`;
                
                document.getElementById('file-path-msg').innerHTML = msg;
            });
        }

        function voltarUpload() {
            document.getElementById('step-2').classList.add('d-none');
            document.getElementById('step-1').classList.remove('d-none');
            document.getElementById('file-input').value = '';
        }

        function novoUpload() {
            location.reload();
        }
    </script>
</body>
</html>
