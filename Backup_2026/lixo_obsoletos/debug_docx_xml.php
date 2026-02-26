<?php
// debug_docx_xml.php
// Lê diretamente o XML do .docx para encontrar variáveis ${...}

$files = [
    'modelos_prod/ModeloProfissionalV2.docx',
    'modelos_prod/ModeloPropostaDrone.docx',
    'modelos_prod/ModeloPropostaPadrao.docx'
];

echo "<h1>Análise de Variáveis DOCX (XML Raw)</h1>";

foreach ($files as $file) {
    echo "<h2>Arquivo: $file</h2>";
    
    if (!file_exists($file)) {
        echo "<p style='color:red'>Arquivo não encontrado.</p><hr>";
        continue;
    }

    $zip = new ZipArchive;
    if ($zip->open($file) === TRUE) {
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        
        // Regex para encontrar padrões ${...}
        // Nota: O XML do Word muitas vezes quebra a string (ex: $<tag>{<tag>var<tag>}), 
        // então essa regex simples pode falhar em casos complexos, mas pega a maioria dos "limpos".
        // Uma regex mais robusta para XML sujo seria muito complexa para um script simples.
        
        // Tentativa 1: Padrão limpo
        preg_match_all('/\$\{([a-zA-Z0-9_]+)\}/', $xml, $matches);
        
        // Tentativa 2: Padrão com tags no meio (básico)
        // Procura por $ { ... } ignorando tags
        // (Isso é difícil de fazer com regex simples em PHP sem DOM)
        
        $found = array_unique($matches[1]);
        sort($found);
        
        if (!empty($found)) {
            echo "<ul>";
            foreach($found as $var) {
                echo "<li>\${<strong>$var</strong>}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Nenhuma variável no formato <code>\${var}</code> encontrada (pode estar quebrada por tags XML).</p>";
        }
        
        // DEBUG: Mostrar trechos próximos a 'Valor' para ver como está escrito
        echo "<h3>Contexto 'Valor' (Raw XML dump parcial):</h3>";
        $offset = 0;
        while (($pos = strpos($xml, 'Valor', $offset)) !== false) {
            $excerpt = substr($xml, max(0, $pos - 50), 100);
            echo "<code>... " . htmlspecialchars($excerpt) . " ...</code><br>";
            $offset = $pos + 1;
            if ($offset > strlen($xml)) break;
        }

    } else {
        echo "<p style='color:red'>Falha ao abrir ZIP (DOCX inválido?).</p>";
    }
    echo "<hr>";
}
?>
