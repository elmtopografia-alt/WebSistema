<?php
// Nome do Arquivo: debug_nome.php
// Função: Mostrar como o sistema está "limpando" o nome do serviço Retificação.

session_start();
require_once 'config.php';
require_once 'db.php';

header('Content-Type: text/html; charset=utf-8'); // Força exibir acentos corretos no navegador

// Função de Limpeza (Cópia exata do seu sistema)
function limparStr($string) {
    $s = trim($string);
    if (function_exists('iconv')) $s = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
    return preg_replace('/[^a-zA-Z0-9]/', '', $s);
}

$conn = Database::getProd();

echo "<h3>Análise do Serviço 'Retificação de Área'</h3>";

// Busca especificamente serviços que contenham "Retifica"
$sql = "SELECT nome FROM Tipo_Servicos WHERE nome LIKE '%Retifica%'";
$res = $conn->query($sql);

if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $original = $row['nome'];
        $limpo = limparStr($original);
        $arquivo_final = "ModeloProposta" . $limpo . ".docx";
        
        echo "<p><strong>Nome no Banco:</strong> " . $original . "</p>";
        echo "<p><strong>Nome Limpo (Pelo PHP):</strong> <span style='color:blue; font-size:1.2em;'>" . $limpo . "</span></p>";
        echo "<p><strong>Arquivo que o sistema busca:</strong> <span style='color:red; font-weight:bold;'>" . $arquivo_final . "</span></p>";
        echo "<hr>";
        
        // Verifica se existe na pasta
        $caminho = __DIR__ . '/modelos_prod/' . $arquivo_final;
        if (file_exists($caminho)) {
            echo "<p style='color:green; font-weight:bold;'>STATUS: ARQUIVO ENCONTRADO NA PASTA! ✅</p>";
        } else {
            echo "<p style='color:red; font-weight:bold;'>STATUS: ARQUIVO NÃO EXISTE NA PASTA! ❌</p>";
            echo "<p>Solução: Renomeie seu arquivo Word no computador exatamente para: <code>$arquivo_final</code> e suba novamente.</p>";
        }
    }
} else {
    echo "Nenhum serviço com nome 'Retifica' encontrado no banco.";
}
?>