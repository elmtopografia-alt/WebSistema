<?php
/**
 * salvar_proposta.php - Versão Refatorada (Fase 2)
 * Único endpoint para criar, editar e revisar propostas
 */

require_once __DIR__ . '/session_validator.php';
require_once __DIR__ . '/ConnectionManager.php';
require_once __DIR__ . '/PropostaRepository.php';
require_once __DIR__ . '/config.php';

// Debug de Entrada
error_log("--- ACESSO salvar_proposta.php [" . $_SERVER['REQUEST_METHOD'] . "] ---");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST DATA: " . json_encode($_POST));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("<h1>Erro: Método Inválido</h1><p>Este script espera uma requisição POST.</p><button onclick='history.back()'>Voltar</button>");
}

try {
    validarCsrf();
    
    $repo = new PropostaRepository();
    
    // Detecta se é uma nova REVISÃO (id_proposta_original) ou um UPDATE simples (id_proposta_criada/id_proposta)
    $idOriginal = !empty($_POST['id_proposta_original']) ? intval($_POST['id_proposta_original']) : null;
    
    // Processa o salvamento (toda a lógica centralizada no Repository)
    $id = $repo->salvar($_POST, $idOriginal);
    
    // Detecta se é requisição AJAX (para evitar problemas com target=_blank e sessões)
    $isAjax = !empty($_POST['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    
    // Redireciona conforme formato solicitado
    $formato = $_POST['formato_saida'] ?? 'docx';
    
    // Define URL de destino
    $redirectUrl = '';
    switch ($formato) {
        case 'editor':
            $_SESSION['id_proposta_ativa'] = $id;
            $redirectUrl = "editor_dinamico.php?id=$id&success=1";
            break;
        case 'html':
            $redirectUrl = "gerar_proposta_html.php?id=$id";
            break;
        // Novo formato de teste: usa o motor premium isolado (crm-propostas)
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
            'id' => $id
        ]);
        exit;
    } else {
        header("Location: $redirectUrl");
        exit;
    }
    
} catch (Throwable $e) {
    // Detecta AJAX no erro também
    $isAjax = !empty($_POST['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

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

    // Modo Debug: Mostrar erro na tela
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