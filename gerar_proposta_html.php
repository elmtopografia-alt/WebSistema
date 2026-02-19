<?php
/**
 * GERADOR DE PROPOSTA HTML - VERSÃO DEFENSIVA
 * Este arquivo prepara os dados e chama o renderizador oficial com logs detalhados.
 */

// 1. Configurações Iniciais de Erro (Apenas para diagnóstico)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// [NOVO] Autoload do Composer para classes do namespace ProposalArchitect
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

$logFile = __DIR__ . '/logs/proposta_errors.log';

function logger($msg) {
    global $logFile;
    $date = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$date] $msg\n", FILE_APPEND);
}

try {
    logger("--- Início do processamento da proposta ---");
    
    // Inclusão de dependências
    require_once 'gerar_proposta_html.WRAPPER.php';
    require_once 'PropostaRepository.php';
    require_once 'ConnectionManager.php';

    // 2. Captura do ID
    if (!isset($_GET['id'])) {
        logger("ERRO: ID não fornecido via GET.");
        die("Erro: ID da proposta não fornecido.");
    }

    $id = intval($_GET['id']);
    logger("Processando Proposta ID: $id");

    // 3. Busca de Dados (Se não houver POST)
    if (empty($_POST) || !isset($_POST['id_servico'])) {
        logger("Buscando dados no repositório para o ID: $id");
        $repo = new PropostaRepository();
        $dados = $repo->buscarPorId($id);
        
        if ($dados) {
            logger("Dados encontrados com sucesso.");
            $_POST = $dados; // Mapeia para o renderizador
        } else {
            logger("ERRO: Proposta #$id não encontrada no banco.");
            die("Erro: Proposta #$id não encontrada no banco de dados.");
        }
    }

    // 4. Lógica de Renderização
    $modeloDocx = $_POST['modelo_docx'] ?? null;

    if ($modeloDocx) {
        logger("Usando renderizador DOCX: $modeloDocx");
        require_once 'renderizador_modelo_docx.php';
        $renderer = new RenderizadorModeloDOCX(ConnectionManager::get());
        echo $renderer->renderizar($modeloDocx, $_SESSION['usuario_id'] ?? 0, $_POST);
        logger("Renderização DOCX concluída.");
        exit;
    }

    // Renderizador HTML Tradicional
    if (file_exists('gerar_documento_html.php')) {
        logger("Chamando renderizador HTML tradicional.");
        require_once 'gerar_documento_html.php';
        logger("Renderização HTML concluída.");
    } else {
        logger("ERRO CRÍTICO: gerar_documento_html.php não encontrado.");
        die("Erro Crítico: Renderizador HTML não encontrado.");
    }

} catch (Throwable $e) {
    $errorMsg = "EXCEÇÃO CAPTURADA: " . $e->getMessage() . " em " . $e->getFile() . " na linha " . $e->getLine();
    logger($errorMsg);
    echo "<div style='color:red; background:#fee; padding:15px; border:1px solid red; font-family:sans-serif;'>";
    echo "<h3>Ocorreu um erro ao gerar a proposta</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
