<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/ConnectionManager.php';
require_once __DIR__ . '/PropostaRepository.php';
require_once __DIR__ . '/renderizador_modelo_docx.php';

try {
    $repo = new PropostaRepository();
    $docxRenderer = new RenderizadorModeloDOCX($repo->getConn());
    $modelosDisponiveis = $docxRenderer->listarModelos();
    
    echo "<h1>TESTE DE CARREGAMENTO</h1>";
    echo "<pre>Modelos encontrados: " . print_r($modelosDisponiveis, true) . "</pre>";
    
    // Tentar carregar classe
    $arquivo = __DIR__ . '/modelos_gerados/ModeloPropostaDrone.php';
    if(file_exists($arquivo)) {
        require_once $arquivo;
        echo "<p style='color:green'>Arquivo ModeloPropostaDrone.php lido sem erros fatais.</p>";
        
        if (class_exists("\\SGT\\Propostas\\ModeloPropostaDrone")) {
             echo "<p style='color:green'>Classe ModeloPropostaDrone INSTANCIADA com sucesso.</p>";
        } else {
             echo "<p style='color:red'>Classe não detectada!</p>";
        }
    } else {
        echo "<p style='color:red'>Arquivo Modelo não encontrado: {$arquivo}</p>";
    }
    
} catch (Exception $e) {
    echo "<h3>Erro capturado:</h3><pre>" . $e->getMessage() . "</pre>";
}
echo "<p>Fim do roteiro de teste</p>";
?>
