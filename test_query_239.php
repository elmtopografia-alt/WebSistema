<?php
require_once __DIR__ . '/ConnectionManager.php';
$conn = ConnectionManager::get();
$id = 239;

echo "<h1>Teste de Query Exata - Proposta 239</h1>";

$sql = "SELECT p.*, c.nome_cliente, c.email as email_cliente, c.telefone as telefone_cliente, c.celular as celular_cliente,
               s.nome as nome_servico, d.Empresa as nome_empresa, d.logo_caminho as logo_empresa,
               p.modelo_docx,
               p.docx_conteudo,
               p.docx_blocos_count,
               p.docx_ultima_edicao
        FROM Propostas p 
        LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente 
        LEFT JOIN Tipo_Servicos s ON p.id_servico = s.id_servico
        LEFT JOIN DadosEmpresa d ON p.id_criador = d.id_criador
        WHERE p.id_proposta = $id";

$res = $conn->query($sql);
if (!$res) {
    die("<p style='color:red'>❌ ERRO NA QUERY PRINCIPAL: " . $conn->error . "</p><pre>$sql</pre>");
}

$dados = $res->fetch_assoc();
if (!$dados) {
    die("<p style='color:red'>❌ PROPOSTA NÃO ENCONTRADA COM ESTA QUERY (Query retornou zero linhas).</p>");
}

echo "<p style='color:green'>✅ Query principal funcionou!</p>";

// Testar query de locação
$sql_loc = "SELECT pl.*, m.nome_marca as marca, tl.nome as tipo 
            FROM Proposta_Locacao pl 
            LEFT JOIN Marcas m ON pl.id_marca = m.id_marca
            LEFT JOIN Tipo_Locacao tl ON pl.id_locacao = tl.id_locacao
            WHERE pl.id_proposta = $id";

$res_loc = $conn->query($sql_loc);
if (!$res_loc) {
    echo "<p style='color:red'>❌ ERRO NA QUERY DE LOCAÇÃO: " . $conn->error . "</p>";
} else {
    echo "<p style='color:green'>✅ Query de locação funcionou!</p>";
}
?>
