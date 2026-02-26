<?php
// ATIVA EXIBIÇÃO TOTAL DE ERROS
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico de Erro 500</h1>";
echo "<p>Se você está vendo esta mensagem, o PHP básico está funcionando.</p>";

// 1. TENTA CONECTAR AO BANCO
echo "<h2>1. Teste de Conexão com Banco de Dados</h2>";
try {
    if (!file_exists('db.php')) {
        throw new Exception("Arquivo db.php não encontrado.");
    }
    require_once 'db.php';
    echo "Arquivo db.php carregado.<br>";
    
    if (class_exists('Database')) {
        $conn = Database::getProd();
        echo "Conexão com Banco de Dados: <span style='color:green'>SUCESSO</span><br>";
    } else {
        echo "Classe Database não encontrada.<br>";
    }
} catch (Throwable $e) {
    echo "Erro no Banco: <span style='color:red'>" . $e->getMessage() . "</span><br>";
}

// 2. TENTA CARREGAR ARQUIVO GERADOR (SEM EXECUTAR A LÓGICA PESADA)
echo "<h2>2. Teste de Sintaxe do Gerador</h2>";
$arquivoGerador = 'gerar_documento_html.php';

if (file_exists($arquivoGerador)) {
    // Tenta ler o arquivo para ver se tem erro de sintaxe (linting básico via output buffer)
    $conteudo = file_get_contents($arquivoGerador);
    if (strpos($conteudo, '<?php') === false) {
        echo "Aviso: O arquivo parece não ter tag PHP de abertura correta.<br>";
    }
    
    // Tenta incluir (vai falhar se tiver erro de parse)
    try {
        // Define variáveis para evitar notices
        $_GET['id'] = 1; // ID fictício para teste
        $_GET['piloto'] = 'novo'; 
        
        // Output buffering para não mostrar o HTML do gerador agora
        ob_start();
        include $arquivoGerador;
        ob_end_clean();
        
        echo "Inclusão do arquivo: <span style='color:green'>SUCESSO (Sem erros fatais de sintaxe)</span><br>";
    } catch (Throwable $e) {
        ob_end_clean();
        echo "Erro Fatal ao carregar o gerador: <span style='color:red'>" . $e->getMessage() . "</span><br>";
        echo "Arquivo: " . $e->getFile() . " na linha " . $e->getLine() . "<br>";
    }
} else {
    echo "Erro: Arquivo $arquivoGerador não encontrado.<br>";
}

echo "<hr>";
echo "<p>Se houver erros acima, tire um print e me envie.</p>";
?>
