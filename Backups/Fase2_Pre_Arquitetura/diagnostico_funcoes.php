<?php
require_once 'config.php';
require_once 'db.php';

$conn = Database::getProd();

echo "<h2>🔍 Diagnóstico da Tabela Tipo_Funcoes</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .card { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background-color: #2563eb; color: white; }
    tr:nth-child(even) { background-color: #f9f9f9; }
    .status { padding: 5px 10px; border-radius: 4px; font-weight: bold; }
    .ok { background: #d4edda; color: #155724; }
    .erro { background: #f8d7da; color: #721c24; }
</style>";

// 1. Verificar se a tabela existe
echo "<div class='card'>";
echo "<h3>1. Verificação da Tabela</h3>";
$checkTable = $conn->query("SHOW TABLES LIKE 'Tipo_Funcoes'");
if ($checkTable && $checkTable->num_rows > 0) {
    echo "<p class='status ok'>✅ Tabela 'Tipo_Funcoes' existe</p>";
} else {
    echo "<p class='status erro'>❌ Tabela 'Tipo_Funcoes' NÃO encontrada!</p>";
    exit;
}
echo "</div>";

// 2. Contar registros
echo "<div class='card'>";
echo "<h3>2. Total de Registros</h3>";
$count = $conn->query("SELECT COUNT(*) as total FROM Tipo_Funcoes");
$totalRegistros = $count->fetch_assoc()['total'];
echo "<p><strong>Total de funções cadastradas:</strong> $totalRegistros</p>";
if ($totalRegistros == 0) {
    echo "<p class='status erro'>⚠️ ATENÇÃO: Nenhuma função cadastrada no banco!</p>";
} else {
    echo "<p class='status ok'>✅ Existem funções cadastradas</p>";
}
echo "</div>";

// 3. Listar todas as funções
echo "<div class='card'>";
echo "<h3>3. Funções Cadastradas</h3>";
$result = $conn->query("SELECT * FROM Tipo_Funcoes ORDER BY nome ASC");

if ($result && $result->num_rows > 0) {
    echo "<table>";
    echo "<tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Salário Base</th>
        <th>Data Criação</th>
    </tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id_funcao']}</td>";
        echo "<td><strong>{$row['nome']}</strong></td>";
        echo "<td>R$ " . number_format($row['salario_base_default'], 2, ',', '.') . "</td>";
        echo "<td>" . ($row['created_at'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='status erro'>❌ Nenhum registro retornado pela consulta</p>";
}
echo "</div>";

// 4. Verificar estrutura da tabela
echo "<div class='card'>";
echo "<h3>4. Estrutura da Tabela</h3>";
$structure = $conn->query("DESCRIBE Tipo_Funcoes");
echo "<table>";
echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
while ($col = $structure->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$col['Field']}</td>";
    echo "<td>{$col['Type']}</td>";
    echo "<td>{$col['Null']}</td>";
    echo "<td>{$col['Key']}</td>";
    echo "<td>{$col['Default']}</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// 5. Testar query usada no editar_proposta.php
echo "<div class='card'>";
echo "<h3>5. Teste da Query do Sistema</h3>";
$testQuery = "SELECT * FROM Tipo_Funcoes ORDER BY nome ASC";
echo "<p><code>$testQuery</code></p>";
$testResult = $conn->query($testQuery);
if ($testResult) {
    echo "<p class='status ok'>✅ Query executada com sucesso</p>";
    echo "<p>Registros retornados: <strong>{$testResult->num_rows}</strong></p>";
} else {
    echo "<p class='status erro'>❌ Erro ao executar query: " . $conn->error . "</p>";
}
echo "</div>";

?>
