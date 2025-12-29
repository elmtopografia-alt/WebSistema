<?php
// Nome do Arquivo: debug_modelos.php
// Função: Diagnóstico de Arquivos de Modelo.

session_start();
require_once 'config.php';
require_once 'db.php';

echo "<h1>Diagnóstico de Modelos (.docx)</h1>";

// Define ambiente
$pasta = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') ? 'modelos_demo/' : 'modelos_prod/';
$dir = __DIR__ . '/' . $pasta;

echo "<h3>Verificando pasta: <code>$pasta</code></h3>";

// Função de Limpeza (A MESMA DO SISTEMA)
function limparStr($string) {
    $s = trim($string);
    if (function_exists('iconv')) $s = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
    return preg_replace('/[^a-zA-Z0-9]/', '', $s);
}

// Lista arquivos reais na pasta
$arquivos_reais = [];
if (is_dir($dir)) {
    $scanned = scandir($dir);
    foreach ($scanned as $file) {
        if ($file !== '.' && $file !== '..') {
            $arquivos_reais[] = $file;
        }
    }
} else {
    echo "<p style='color:red'>ERRO: A pasta não existe!</p>";
}

// Conecta e busca serviços
$conn = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') ? Database::getDemo() : Database::getProd();
$sql = "SELECT nome FROM Tipo_Servicos ORDER BY nome ASC";
$res = $conn->query($sql);

echo "<table border='1' cellpadding='10' style='border-collapse:collapse; width:100%; font-family: sans-serif;'>";
echo "<tr style='background:#ddd; text-align:left;'><th>Serviço (Banco)</th><th>Nome Esperado pelo Sistema</th><th>Status</th></tr>";

while ($row = $res->fetch_assoc()) {
    $nome_servico = $row['nome'];
    $nome_limpo = limparStr($nome_servico);
    $esperado = "ModeloProposta" . $nome_limpo . ".docx";
    
    $encontrado = in_array($esperado, $arquivos_reais);
    
    $cor = $encontrado ? '#d4edda' : '#f8d7da';
    $msg = $encontrado ? '<b style="color:green">OK (Encontrado)</b>' : '<b style="color:red">ERRO (Não achou)</b>';
    
    echo "<tr style='background:$cor'>";
    echo "<td>$nome_servico</td>";
    echo "<td>$esperado</td>";
    echo "<td>$msg</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Arquivos que estão na pasta agora (Copie o nome daqui se precisar renomear):</h3>";
echo "<pre>";
print_r($arquivos_reais);
echo "</pre>";
?>