<?php
/**
 * ARQUIVO: atualizar_proposta.php (UPGRADE v2.0)
 * OBJETIVO: Atualizar proposta existente com planilha de custos completa
 * DIFERENÇA do salvar: UPDATE em vez de INSERT, mantém ID e data de criação
 */

require_once __DIR__ . '/session_validator.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/PropostaRepository.php';

// Validações básicas
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido');
}

// CSRF
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Token de segurança inválido');
}

$id_usuario = $_SESSION['usuario_id'] ?? 0;
$id_proposta = (int)($_POST['id_proposta'] ?? 0);

if (!$id_usuario || !$id_proposta) {
    header('Location: painel.php?erro=dados_invalidos');
    exit;
}

try {
    $repo = new PropostaRepository();
    
    // Atualiza proposta completa (usa o mesmo extrairDadosPlanilha do salvar)
    $sucesso = $repo->atualizarCompleto($id_proposta, $_POST, $id_usuario);
    
    if (!$sucesso) {
        throw new Exception("Não foi possível atualizar a proposta");
    }
    
    // Log de auditoria (opcional)
    error_log("Proposta #{$id_proposta} atualizada pelo usuário #{$id_usuario} em " . date('Y-m-d H:i:s'));
    
    // Redirecionamento baseado na ação ou formato_saida
    $acao = $_POST['acao'] ?? 'salvar';
    $formato = $_POST['formato_saida'] ?? '';

    // Se o JS sinalizou que devemos ir para o editor avançado
    if ($formato === 'editor') {
        $modelo = $_POST['modelo_docx'] ?? 'PropostaDrone';
        header("Location: editor_dinamico.php?id={$id_proposta}&modelo_docx=" . urlencode($modelo) . "&msg=atualizado");
        exit;
    }
    
    switch ($acao) {
        case 'salvar_visualizar':
            // Salva e vai para visualização
            header("Location: visualizar_proposta.php?id={$id_proposta}&msg=atualizado");
            break;
            
        case 'salvar_editor':
            // Salva e vai para editor de documento
            header("Location: editor_dinamico.php?id={$id_proposta}&msg=atualizado");
            break;
            
        case 'salvar_nova':
            // Salva e cria nova proposta
            header("Location: criar_proposta_dinamica.php?nova=1&msg=atualizado&anterior={$id_proposta}");
            break;
            
        case 'salvar':
        default:
            // Salva e volta para painel
            header("Location: painel.php?msg=proposta_atualizada&id={$id_proposta}");
            break;
    }
    
    exit;
    
} catch (Exception $e) {
    error_log("Erro ao atualizar proposta #{$id_proposta}: " . $e->getMessage());
    
    // Redireciona com mensagem de erro
    header("Location: editar_proposta.php?id={$id_proposta}&erro=" . urlencode($e->getMessage()));
    exit;
}