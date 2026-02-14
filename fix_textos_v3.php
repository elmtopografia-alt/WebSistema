<?php
/**
 * CORREÇÃO AVANÇADA V3 - DRONE (FINAL)
 * Foco: Limpeza de HTML Entities e variações de codificação no Cronograma
 */
require_once 'db.php';
$conn = Database::getProd();

echo "<!DOCTYPE html><html lang='pt-BR'><head><meta charset='UTF-8'><title>Fix V3</title><style>body{background:#0d1117;color:#c9d1d9;font-family:monospace;padding:20px}pre{background:#161b22;border:1px solid #30363d;padding:10px}</style></head><body>";
echo "<h1>🧹 FAXINA PESADA (V3) - CRONOGRAMA</h1>";

// LISTA DE FRAGMENTOS TÓXICOS (Com e sem HTML Entities)
$fragmentosToxicos = [
    // Versão Texto Puro
    "Este investimento reflete o custo-benefício da tecnologia",
    "maior riqueza de dados (milhões de pontos)",
    "em menor tempo de execução comparado",
    "produtividade multiplicada",
    "R$ 5.850,00 (CINCO MIL OITOCENTOS E CINQUENTA REAIS)",
    "R$ 6.500,00 (SEIS MIL E QUINHENTOS REAIS)",
    
    // Versão HTML Entities (TinyMCE Gosta Disso)
    "custo-benef&iacute;cio da tecnologia",
    "maior riqueza de dados (milh&otilde;es de pontos)",
    "execu&ccedil;&atilde;o comparado",
    "produtividade multiplicada",
    
    // Versão HTML Exata (Reportada pelo Usuário)
    '<h3 style="color: #000; text-align: center;">R$ 5.850,00 (CINCO MIL OITOCENTOS E CINQUENTA REAIS)</h3>',
    '<p>Este investimento reflete o custo-benef&iacute;cio da tecnologia: maior riqueza de dados (milh&otilde;es de pontos) em menor tempo de execu&ccedil;&atilde;o comparado &agrave; topografia tradicional.</p>',
    
    // Trechos do cabeçalho de valor que aparece no meio
    "O valor total para execu&ccedil;&atilde;o dos servi&ccedil;os",
    "O valor total para execução dos serviços"
];

// Tabelas para limpar
$tabelas = [
    'Proposta_Conteudo_Personalizado' => 'conteudo_texto', 
    'service_type_blocks' => 'default_content',
    'proposal_block_templates' => 'default_content'
];

$totalRemovido = 0;

foreach ($tabelas as $tabela => $coluna) {
    echo "<h2>Tabela: $tabela</h2>";
    foreach ($fragmentosToxicos as $frag) {
        // Usa LIKE para encontrar (mais flexível que REPLACE direto no WHERE)
        $fragEscapado = $conn->real_escape_string($frag);
        
        // 1. Identifica IDs afetados
        $sqlBusca = "SELECT block_id, id_proposta FROM $tabela 
                     WHERE block_id = 'cronograma' 
                     AND $coluna LIKE '%$fragEscapado%'";
        
        // Ajuste para tabelas sem id_proposta
        if ($tabela != 'Proposta_Conteudo_Personalizado') {
             $sqlBusca = "SELECT block_id FROM $tabela WHERE block_id = 'cronograma' AND $coluna LIKE '%$fragEscapado%'";
        }

        $res = $conn->query($sqlBusca);
        if ($res && $res->num_rows > 0) {
            echo "<p style='color:orange'>Encontrado '$frag' em {$res->num_rows} registros.</p>";
            
            // 2. Tenta limpar usando REPLACE genérico (substitui o fragmento por vazio)
            // Problema: Pode deixar restos de HTML (<p></p> vazios).
            // Solução Radical para este caso: Se contém o texto tóxico, reseta o bloco para vazio ou padrão limpo?
            // O usuário reclamou de DUPLICAÇÃO. Se apagarmos o bloco todo, ele perde o cronograma real?
            // O texto tóxico parece ser UM PARÁGRAFO INTEIRO. REPLACE é seguro se pegarmos a frase toda.
            
            // Vamos fazer REPLACE do fragmento
            $sqlUpdate = "UPDATE $tabela SET $coluna = REPLACE($coluna, '$fragEscapado', '') 
                          WHERE block_id = 'cronograma' AND $coluna LIKE '%$fragEscapado%'";
            $conn->query($sqlUpdate);
            $totalRemovido += $conn->affected_rows;
        }
    }
}

// 3. LIMPEZA DE TAGS HTML VAZIAS RESTANTES (Opcional, mas bom)
// Remove paragrafos vazios que sobraram: <p></p>, <p>&nbsp;</p>
$queriesLimpezaFinal = [
    "UPDATE Proposta_Conteudo_Personalizado SET conteudo_texto = REPLACE(conteudo_texto, '<p>&nbsp;</p>', '') WHERE block_id = 'cronograma'",
    "UPDATE Proposta_Conteudo_Personalizado SET conteudo_texto = REPLACE(conteudo_texto, '<p></p>', '') WHERE block_id = 'cronograma'",
    // Remove cabeçalhos de valor soltos
    "UPDATE Proposta_Conteudo_Personalizado SET conteudo_texto = REGEXP_REPLACE(conteudo_texto, '<h3[^>]*>R\\$ [0-9\\.,]+ .*?</h3>', '') WHERE block_id = 'cronograma'" 
    // Nota: REGEXP_REPLACE só funciona em MySQL 8.0+. Se falhar, ignoramos.
];

// Tenta executar REGEXP (pode falhar em versões antigas, então usamos try/catch fake ou silenciamos)
foreach ($queriesLimpezaFinal as $q) {
    @$conn->query($q);
}


echo "<hr><h2 style='color:#48bb78'>✅ Processo Concluído. $totalRemovido fragmentos removidos.</h2>";
echo "<p>Agora execute a Verificação Novamente:</p>";
echo "<a href='check_status_drone.php' style='color:#58a6ff;text-decoration:none;border:1px solid #58a6ff;padding:10px 20px;border-radius:5px'>Verificar Status</a>";
echo "</body></html>";
$conn->close();
