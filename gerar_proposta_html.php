<?php
/**
 * GERADOR DE PROPOSTA HTML - PONTE DE DADOS
 * Este arquivo prepara os dados e chama o renderizador oficial.
 */

require_once 'gerar_proposta_html.WRAPPER.php';
require_once 'PropostaRepository.php';

// 1. Se veio um ID via GET mas não tem POST (clique direto em link)
// Ou se o POST está vazio, buscamos os dados persistidos no banco.
// 1. Se veio um ID via GET mas não tem POST (clique direto em link)
// Ou se o POST está vazio, buscamos os dados persistidos no banco.
if (isset($_GET['id']) && (empty($_POST) || !isset($_POST['id_servico']))) {
    // [FIX] Relaxamento de CSRF para visualização (apenas leitura)
    // Como é uma página de visualização pública (ou restrita por sessão), não exigimos token CSRF estrito aqui.
    
    $id = intval($_GET['id']);
    $repo = new PropostaRepository();
    $dados = $repo->buscarPorId($id);
    
    if ($dados) {
        // Mapeamos os dados salvos para o formato que o renderizador espera ($_POST)
        $_POST = $dados;
    } else {
        die("Erro: Proposta #$id não encontrada no banco de dados.");
    }
}

// 2. Chama o renderizador oficial (que já contém os ajustes de layout do usuário)
if (file_exists('gerar_documento_html.php')) {
    require_once 'gerar_documento_html.php';
} else {
    die("Erro Crítico: Renderizador 'gerar_documento_html.php' não encontrado.");
}
