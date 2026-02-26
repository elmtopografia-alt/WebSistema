<?php
// Habilita exibição de erros IMEDIATAMENTE
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

echo "<h1>Diagnóstico de Acesso</h1>";

try {
    echo "<p>Tentando incluir db.php...</p>";
    if (!file_exists('db.php')) {
        throw new Exception("db.php não encontrado!");
    }
    require_once 'db.php';
    echo "<p style='color:green'>db.php incluído.</p>";

    if (!isset($conn) || !($conn instanceof mysqli)) {
        // Tenta obter manualmente se a variavel global falhou
        echo "<p>Variável global \$conn inválida. Tentando Database::getProd()...</p>";
        $conn = Database::getProd();
    }

    if ($conn->connect_error) {
        throw new Exception("Erro de Conexão: " . $conn->connect_error);
    }
    echo "<p style='color:green'>Conexão DB OK.</p>";

    $res = $conn->query("SELECT id_proposta, numero_proposta FROM Propostas ORDER BY id_proposta DESC LIMIT 5");
    
    if (!$res) {
        throw new Exception("Erro na Query: " . $conn->error);
    }

    echo "<h3>Últimas Propostas (Teste de Link Direto)</h3>";
    echo "<ul>";
    while ($row = $res->fetch_assoc()) {
        $id = $row['id_proposta'];
        $nome = 'Cliente #' . ($row['id_cliente'] ?? '?');
        echo "<li>";
        echo "ID: <strong>$id</strong> | Cliente: $nome ";
        echo "<br><a href='gerar_proposta_html.php?id=$id' target='_blank' style='background:blue;color:white;padding:5px;border-radius:4px;text-decoration:none;'>[ABRIR HTML]</a> ";
        echo " <a href='gerar_proposta_html.php?id=$id' target='_self' style='font-size:0.8em'>[Mesma Aba]</a>";
        echo "</li><br>";
    }
    echo "</ul>";

} catch (Throwable $e) {
    echo "<div style='background:#fee;border:1px solid red;padding:20px;color:red'>";
    echo "<h2>ERRO FATAL</h2>";
    echo "Mensagem: " . $e->getMessage() . "<br>";
    echo "Arquivo: " . $e->getFile() . " (Linha " . $e->getLine() . ")";
    echo "</div>";
}
?>
