<?php
/**
 * SGT Upgrade v2.0 - Executor de ROLLBACK
 *REMOVE as 290 colunas de custos na tabela Propostas. Use com cautela!
 */

require_once 'session_validator.php';
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) die("Acesso negado.");

$conn = Database::getProd();
$sqlFile = 'rollback_v2.0.sql';

if (!file_exists($sqlFile)) die("Erro: Arquivo $sqlFile não encontrado.");

$sql = file_get_contents($sqlFile);
$queries = explode(';', $sql);

echo "<h2>ATENÇÃO: Rollback SGT v2.0</h2>";

foreach ($queries as $query) {
    if (trim($query) == "") continue;
    try {
        $conn->query($query);
        echo "<li style='color:orange'>Executada: " . substr(trim($query), 0, 50) . "...</li>";
    } catch (Exception $e) {
        echo "<li style='color:red'>Erro em: " . substr(trim($query), 0, 50) . "... -> " . $e->getMessage() . "</li>";
    }
}

echo "<h3>Rollback concluído. As colunas v2.0 foram removidas.</h3>";
?>
