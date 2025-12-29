<?php
// Nome do Arquivo: verificar_nomes.php
// Função: Lista os serviços do banco e mostra qual nome de arquivo o sistema está esperando.

require_once 'config.php';
require_once 'db.php';

// Função de Limpeza (A MESMA DO SISTEMA)
function limparStr($string) {
    $string = str_replace(
        ['Á', 'À', 'Â', 'Ã', 'Ä', 'á', 'à', 'â', 'ã', 'ä', 'É', 'È', 'Ê', 'Ë', 'é', 'è', 'ê', 'ë', 'Í', 'Ì', 'Î', 'Ï', 'í', 'ì', 'î', 'ï', 'Ó', 'Ò', 'Ô', 'Õ', 'Ö', 'ó', 'ò', 'ô', 'õ', 'ö', 'Ú', 'Ù', 'Û', 'Ü', 'ú', 'ù', 'û', 'ü', 'Ç', 'ç', 'Ñ', 'ñ'],
        ['A', 'A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a', 'E', 'E', 'E', 'E', 'e', 'e', 'e', 'e', 'I', 'I', 'I', 'I', 'i', 'i', 'i', 'i', 'O', 'O', 'O', 'O', 'O', 'o', 'o', 'o', 'o', 'o', 'U', 'U', 'U', 'U', 'u', 'u', 'u', 'u', 'C', 'c', 'N', 'n'],
        $string
    );
    return preg_replace('/[^a-zA-Z0-9]/', '', $string);
}

$conn = Database::getProd();
$sql = "SELECT id_servico, nome FROM Tipo_Servicos ORDER BY nome ASC";
$res = $conn->query($sql);

echo "<h1>📋 Lista de Arquivos Necessários</h1>";
echo "<p>Renomeie seus arquivos Word exatamente como abaixo e suba no Admin:</p>";
echo "<table border='1' cellpadding='10'>";
echo "<tr style='background:#ccc'><th>ID</th><th>Nome do Serviço (Banco)</th><th>Nome do Arquivo Esperado (.docx)</th><th>Status no Servidor</th></tr>";

$pasta = __DIR__ . '/modelos_prod/';

while ($row = $res->fetch_assoc()) {
    $limpo = limparStr($row['nome']);
    $arquivo = "ModeloProposta" . $limpo . ".docx";
    $existe = file_exists($pasta . $arquivo);
    
    $status = $existe ? "<span style='color:green'>OK (Encontrado)</span>" : "<span style='color:red; font-weight:bold'>FALTANDO</span>";
    $cor = $existe ? "#d4edda" : "#f8d7da";

    echo "<tr style='background:$cor'>";
    echo "<td>{$row['id_servico']}</td>";
    echo "<td>{$row['nome']}</td>";
    echo "<td><strong>$arquivo</strong></td>";
    echo "<td>$status</td>";
    echo "</tr>";
}
echo "</table>";
?>