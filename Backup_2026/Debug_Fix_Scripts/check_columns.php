<?php
// ARQUIVO: check_columns.php
require_once 'config.php';
require_once 'db.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$conn = Database::getProd();

function describe($table) {
    global $conn;
    echo "--- $table ---\n";
    $res = $conn->query("SHOW COLUMNS FROM $table");
    if($res) {
        while($r = $res->fetch_assoc()) {
            echo $r['Field'] . " (" . $r['Type'] . ")\n";
        }
    } else {
        echo "Erro: " . $conn->error . "\n";
    }
    echo "\n";
}

describe('proposal_content_variations');
describe('proposal_block_templates');
describe('Tipo_Servicos');
?>
