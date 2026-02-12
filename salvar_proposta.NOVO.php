<?php
/**
 * Salvar Proposta - Versão Nova (Segura)
 * 
 * Substituição segura para salvar_proposta.php (PDO + CSRF)
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use SGT\Security\AuthService;
use SGT\Security\CSRFProtection;
use SGT\Security\InputSanitizer;
use SGT\Repository\PropostaRepository;

// Verifica autenticação
AuthService::requireAuth();

try {
    // Valida CSRF
    CSRFProtection::verifyOrFail();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: painel.php');
        exit;
    }
    
    $repo = new PropostaRepository();
    
    // Detecta se é uma nova REVISÃO ou um UPDATE
    $idOriginal = !empty($_POST['id_proposta_original']) ? (int)$_POST['id_proposta_original'] : null;
    
    // O novo Repository usa PDO e já é seguro internaente
    $id = $repo->salvar($_POST, $idOriginal);
    
    // Invalida o token após sucesso para evitar reuso (Opcional, mas recomendado)
    // CSRFProtection::invalidateToken(); 

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
    
} catch (Exception $e) {
    error_log("ERRO salvar_proposta_NOVO: " . $e->getMessage());
    $erro = urlencode("Falha de segurança ou dados: " . $e->getMessage());
    $back = $_SERVER['HTTP_REFERER'] ?? 'painel.php';
    header("Location: $back" . (strpos($back, '?') ? '&' : '?') . "erro=$erro");
}
