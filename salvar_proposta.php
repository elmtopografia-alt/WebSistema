<?php
/**
 * salvar_proposta.php - Versão Refatorada (Fase 2)
 * Único endpoint para criar, editar e revisar propostas
 */

require_once 'session_validator.php';
require_once 'ConnectionManager.php';
require_once 'PropostaRepository.php';
require_once 'config.php';

// Debug de Entrada
error_log("--- ACESSO salvar_proposta.php [" . $_SERVER['REQUEST_METHOD'] . "] ---");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST DATA: " . json_encode($_POST));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("FALHA: Acesso não-POST em salvar_proposta.");
    header('Location: painel.php');
    exit;
}

try {
    validarCsrf();
    
    $repo = new PropostaRepository();
    
    // Detecta se é uma nova REVISÃO (id_proposta_original) ou um UPDATE simples (id_proposta_criada/id_proposta)
    $idOriginal = !empty($_POST['id_proposta_original']) ? intval($_POST['id_proposta_original']) : null;
    
    // Processa o salvamento (toda a lógica centralizada no Repository)
    $id = $repo->salvar($_POST, $idOriginal);
    
    // Redireciona conforme formato solicitado
    $formato = $_POST['formato_saida'] ?? 'docx';
    
    switch ($formato) {
        case 'editor':
            $_SESSION['id_proposta_ativa'] = $id;
            header("Location: editor_dinamico.php?id=$id&success=1");
            break;
        case 'html':
            header("Location: gerar_proposta_html.php?id=$id");
            break;
        default:
            header("Location: proposta_sucesso.php?id=$id");
    }
    
} catch (Throwable $e) {
    error_log("FALHA CRITICA salvar_proposta: " . $e->getMessage() . " em " . $e->getFile() . " na linha " . $e->getLine());
    $erro = urlencode("Erro Interno (Consulte Logs): " . $e->getMessage());
    $back = $_SERVER['HTTP_REFERER'] ?? 'painel.php';
    $sep = strpos($back, '?') !== false ? '&' : '?';
    header("Location: {$back}{$sep}erro={$erro}");
}