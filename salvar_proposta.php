<?php
/**
 * salvar_proposta.php - Versão Refatorada (Fase 3 - Suporte DOCX)
 * Único endpoint para criar, editar e revisar propostas
 * Agora com suporte a campos dinâmicos de modelos DOCX
 */

require_once __DIR__ . '/session_validator.php';
require_once __DIR__ . '/ConnectionManager.php';
require_once __DIR__ . '/PropostaRepository.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Debug de Entrada
error_log("--- ACESSO salvar_proposta.php [" . $_SERVER['REQUEST_METHOD'] . "] ---");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST DATA KEYS: " . implode(', ', array_keys($_POST)));
    // Log específico para detectar modo DOCX
    $isDocxMode = !empty($_POST['modelo_docx']);
    error_log("MODO DOCX: " . ($isDocxMode ? 'SIM (' . $_POST['modelo_docx'] . ')' : 'NÃO'));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("<h1>Erro: Método Inválido</h1><p>Este script espera uma requisição POST.</p><button onclick='history.back()'>Voltar</button>");
}

try {
    validarCsrf();
    
    $repo = new PropostaRepository();
    
    // Detecta se é uma nova REVISÃO (id_proposta_original) ou um UPDATE simples
    $idOriginal = !empty($_POST['id_proposta_original']) ? intval($_POST['id_proposta_original']) : null;
    
    // ============================================================
    // PROCESSAMENTO ESPECIAL MODO DOCX
    // ============================================================
    $modeloDocx = $_POST['modelo_docx'] ?? null;
    $dadosProcessados = $_POST;
    
    if ($modeloDocx) {
        // Extrai e processa campos dinâmicos do DOCX
        $blocosDocx = extrairBlocosDocx($_POST);
        
        if (!empty($blocosDocx)) {
            // Serializa blocos para salvar no banco (JSON ou campo específico)
            $dadosProcessados['docx_blocos_serializado'] = json_encode($blocosDocx);
            $dadosProcessados['docx_modelo_id'] = preg_replace('/[^a-zA-Z0-9]/', '', $modeloDocx);
            
            // Também mantém os campos individuais para compatibilidade
            foreach ($blocosDocx as $index => $bloco) {
                $dadosProcessados["docx_bloco_{$index}_content"] = $bloco['conteudo'];
            }
            
            error_log("DOCX: " . count($blocosDocx) . " blocos extraídos e serializados");
        }
    }
    
    // Processa o salvamento via Repository
    $id = $repo->salvar($dadosProcessados, $idOriginal);
    
    // Se salvou em modo DOCX, garante que o modelo_docx seja persistido
    if ($modeloDocx && $id) {
        $repo->associarModeloDocx($id, $modeloDocx);
    }
    
    // Detecta se é requisição AJAX
    $isAjax = !empty($_POST['ajax']) || 
              (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    
    // Define URL de destino
    $formato = $_POST['formato_saida'] ?? 'docx';
    $redirectUrl = '';
    
    switch ($formato) {
        case 'editor':
            $_SESSION['id_proposta_ativa'] = $id;
            // Se veio de modelo DOCX, redireciona mantendo o contexto
            if ($modeloDocx) {
                $redirectUrl = "editor_dinamico.php?id=$id&modelo_docx=" . urlencode($modeloDocx) . "&success=1";
            } else {
                $redirectUrl = "editor_dinamico.php?id=$id&success=1";
            }
            break;
            
        case 'html':
            $redirectUrl = "gerar_proposta_html.php?id=$id";
            break;
            
        case 'html_premium':
        case 'premium':
            $redirectUrl = "gerar_proposta_premium.php?id=$id";
            break;
            
        default:
            $redirectUrl = "proposta_sucesso.php?id=$id";
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'redirect' => $redirectUrl, 
            'id' => $id,
            'modo_docx' => !empty($modeloDocx),
            'modelo' => $modeloDocx
        ]);
        exit;
    } else {
        header("Location: $redirectUrl");
        exit;
    }
    
} catch (Throwable $e) {
    // Tratamento de erro mantido...
    $isAjax = !empty($_POST['ajax']) || 
              (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

    if ($isAjax) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'error' => $e->getMessage(),
            'debug' => $e->getFile() . ':' . $e->getLine()
        ]);
        exit;
    }

    http_response_code(500);
    echo "<div style='font-family:sans-serif;padding:20px;border:1px solid #ff0000;background:#fff0f0;color:#d00'>";
    echo "<h3>Erro ao Processar Proposta</h3>";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<hr>";
    echo "<button onclick='window.history.back()' style='padding:10px 20px;cursor:pointer'>Voltar e Corrigir</button>";
    echo "</div>";
    
    error_log("FALHA CRITICA salvar_proposta: " . $e->getMessage());
    exit;
}

// ============================================================
// FUNÇÕES AUXILIARES DOCX
// ============================================================

/**
 * Extrai blocos dinâmicos do DOCX dos dados POST
 * Padrão: docx_bloco_{index}_content
 */
function extrairBlocosDocx(array $postData): array {
    $blocos = [];
    
    foreach ($postData as $key => $value) {
        // Match: docx_bloco_0_content, docx_bloco_1_content, etc.
        if (preg_match('/^docx_bloco_(\d+)_content$/', $key, $matches)) {
            $index = intval($matches[1]);
            $blocos[$index] = [
                'index' => $index,
                'conteudo' => $value,
                'nome_campo' => $key
            ];
        }
    }
    
    // Ordena por índice
    ksort($blocos);
    
    return array_values($blocos);
}