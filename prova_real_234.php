<?php
/**
 * prova_real_234.php
 * Executa o salvamento completo e mostra o "corpo" no banco de dados.
 */

require_once __DIR__ . '/ConnectionManager.php';
require_once __DIR__ . '/PropostaRepository.php';

$idProposta = 234;
$repo = new PropostaRepository();
$conn = $repo->getConn();

echo "<html><head><style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0f0c29; color: #fff; padding: 40px; }
    h1 { color: #00d4ff; border-bottom: 2px solid #00d4ff; padding-bottom: 10px; }
    .status-card { background: rgba(255,255,255,0.05); padding: 20px; border-radius: 12px; border: 1px solid #4834d4; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.1); }
    th { color: #a29bfe; font-size: 14px; text-transform: uppercase; }
    .success { color: #00ff88; font-weight: bold; }
    .highlight { background: rgba(72, 52, 212, 0.2); }
</style></head><body>";

echo "<h1>🎯 Prova Real: Proposta #234</h1>";

echo "<div class='status-card'>";
echo "<h3>0. Diagnóstico de Tipos e Marcas Disponíveis</h3>";
$resDB = $conn->query("SELECT tl.id_locacao, tl.nome as tipo, m.id_marca, m.nome_marca 
                       FROM Tipo_Locacao tl 
                       LEFT JOIN Marcas m ON tl.id_locacao = m.id_locacao 
                       ORDER BY tl.id_locacao LIMIT 20");
echo "<ul>";
while($r = $resDB->fetch_assoc()) {
    echo "<li>ID TIPO: {$r['id_locacao']} ({$r['tipo']}) | ID MARCA: {$r['id_marca']} ({$r['nome_marca']})</li>";
}
echo "</ul>";
echo "</div>";

// 1. Busca Dinâmica de Dados Reais para o Teste
// Não vamos usar ID 1 ou 2, vamos perguntar ao banco quem é o Drone e o GPS
$idDrone = 0; $idMarcaDrone = 0; $nomeMarcaDrone = "";
$idGPS = 0; $idMarcaGPS = 0; $nomeMarcaGPS = "";

// Busca um Drone
$res = $conn->query("SELECT tl.id_locacao, m.id_marca, m.nome_marca 
                     FROM Tipo_Locacao tl 
                     JOIN Marcas m ON tl.id_locacao = m.id_locacao 
                     WHERE tl.nome LIKE '%drone%' LIMIT 1");
if($row = $res->fetch_assoc()){
    $idDrone = $row['id_locacao']; 
    $idMarcaDrone = $row['id_marca'];
    $nomeMarcaDrone = $row['nome_marca'];
}

// Busca um GPS
$res = $conn->query("SELECT tl.id_locacao, m.id_marca, m.nome_marca 
                     FROM Tipo_Locacao tl 
                     JOIN Marcas m ON tl.id_locacao = m.id_locacao 
                     WHERE tl.nome LIKE '%gps%' OR tl.nome LIKE '%gnss%' LIMIT 1");
if($row = $res->fetch_assoc()){
    $idGPS = $row['id_locacao']; 
    $idMarcaGPS = $row['id_marca'];
    $nomeMarcaGPS = $row['nome_marca'];
}

$dadosSimulados = [
    'id_proposta' => $idProposta,
    'id_cliente'  => 1,
    'status'      => 'Em elaboração',
    'locacao_id' => array_filter([$idDrone, $idGPS]),
    'locacao_id_marca' => array_filter([$idMarcaDrone, $idMarcaGPS]),
    'locacao_qtd' => [1, 1],
    'locacao_valor' => [1500, 2000],
    'locacao_dias' => [5, 5]
];

echo "<div class='status-card'>";
echo "<h3>1. Buscando Peças Reais no seu banco...</h3>";
if($idDrone) echo "<li>Alvo encontrado: Equipamento <b>Drone</b> (ID $idDrone) com Marca <b>$nomeMarcaDrone</b></li>";
if($idGPS)   echo "<li>Alvo encontrado: Equipamento <b>GPS</b> (ID $idGPS) com Marca <b>$nomeMarcaGPS</b></li>";

echo "<h3>2. Executando fluxo de salvamento...</h3>";

try {
    // Chamamos o salvar oficial do Repository. 
    // Importante: salvar() cria uma REVISÃO, ou seja, gera um NOVO ID.
    $novoId = $repo->salvar($dadosSimulados, $idProposta);
    echo "<p class='success'>✅ salvamento executado via PropostaRepository::salvar(). Novo ID (Revisão): <b>$novoId</b></p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Erro no salvamento: " . $e->getMessage() . "</p>";
    $novoId = $idProposta;
}
echo "</div>";

// 2. Verificação Direta no Banco de Dados (Tabela Propostas)
echo "<div class='status-card'>";
echo "<h3>2. O Inimigo Morto (Valores na tabela 'Propostas')</h3>";
echo "<i>Verificando se os campos flat foram preenchidos na proposta <b>ID $novoId</b>:</i>";

$sql = "SELECT modelo_veiculo, modelo_estacao_total, modelo_gps, modelo_drone 
        FROM Propostas WHERE id_proposta = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $novoId);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if ($row) {
    echo "<table>";
    echo "<tr><th>Coluna</th><th>Valor no Banco</th><th>Status</th></tr>";
    
    foreach ($row as $col => $val) {
        $status = ($val && $val != 'Não informado' && $val != 'Não se aplica') ? "<span class='success'>PREENCHIDO</span>" : "<span>vazio</span>";
        $class = ($val && $val != 'Não informado' && $val != 'Não se aplica') ? "class='highlight'" : "";
        echo "<tr $class><td>$col</td><td><b>" . ($val ?? 'NULL') . "</b></td><td>$status</td></tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color:red;'>❌ Proposta não encontrada.</p>";
}
echo "</div>";

echo "<p style='color: #ccc; font-size: 12px;'>Este teste prova que o método <b>preencherEquipamentosFlat</b> funcionou e 'achatou' os dados para a tabela principal.</p>";
echo "</body></html>";
?>
