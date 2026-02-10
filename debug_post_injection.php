<?php
// debug_post_injection.php
// Script to simulate salvar_proposta.php's injection and output the result
// This verifies if the variables are actually reaching the include context.

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Mock DB connection and classes if needed, or just simulate the flow
// We just want to see if setting $_POST works as expected for an include.

$_POST = []; // Reset
$_POST['id_proposta'] = 123;
$_POST['format'] = 'html';

// Simulate Injection
$_POST['ValorProposta'] = 'R$ 10.000,00';
$_POST['mobilizacao_valor'] = 'R$ 3.000,00';

echo "<h1>Debug: Before Include</h1>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

// Create a dummy target file
file_put_contents('dummy_target.php', '<?php 
echo "<h1>Debug: Inside Include</h1>";
echo "<pre>";
print_r($_POST);
echo "</pre>";
?>');

include 'dummy_target.php';

// Check if render logic works
class MockRenderer {
    private $vars;
    public function __construct($vars) { $this->vars = $vars; }
    public function get($key) { return $this->vars[$key] ?? 'MISSING'; }
}

$renderer = new MockRenderer($_POST);
echo "<h3>Valor via Renderer: " . $renderer->get('ValorProposta') . "</h3>";

unlink('dummy_target.php');
?>
