<?php
// clean_db_content.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require_once 'db.php';

$conn = Database::getProd();

echo "<h3>Iniciando limpeza do banco...</h3>";

$updates = [
    // 1. Remover título "3. Escopo do Serviço"
    "UPDATE proposal_content_variations 
     SET content_text = REPLACE(content_text, '3. Escopo do Serviço', '') 
     WHERE block_slug = 'escopo'",

    // 2. Remover placeholder ${escopo_servico}
    "UPDATE proposal_content_variations 
     SET content_text = REPLACE(content_text, '\${escopo_servico}', '') 
     WHERE block_slug = 'escopo'",

    // 3. Remover placeholder ${empresa_proponente_cidade}
    "UPDATE proposal_content_variations 
     SET content_text = REPLACE(content_text, '\${empresa_proponente_cidade}', '') 
     WHERE block_slug = 'closing'",

    // 4. Remover "Atenciosamente,"
    "UPDATE proposal_content_variations 
     SET content_text = REPLACE(content_text, 'Atenciosamente,', '') 
     WHERE block_slug = 'closing'",

    // 5. Remover título "10. Considerações Finais"
    "UPDATE proposal_content_variations 
     SET content_text = REPLACE(content_text, '10. Considerações Finais', '') 
     WHERE block_slug = 'closing'",,

    // 6. Corrigir texto da Finalidade (Remove texto de terraplenagem e usa a variável)
    "UPDATE proposal_content_variations 
     SET content_text = '\${finalidade}' 
     WHERE block_slug = 'finalidade'"
];

foreach ($updates as $k => $sql) {
    echo "<p>Executando passo " . ($k + 1) . "...<br>";
    if ($conn->query($sql)) {
        echo "<span style='color: green;'>Sucesso! Linhas afetadas: " . $conn->affected_rows . "</span></p>";
    } else {
        echo "<span style='color: red;'>Erro: " . $conn->error . "</span></p>";
    }
}

echo "<h3>Limpeza concluída!</h3> Por favor, recarregue a página do Editor Dinâmico.";
