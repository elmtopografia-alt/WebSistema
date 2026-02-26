<?php
/**
 * debug_variaveis.php
 * Verifica quais variáveis estão sendo mapeadas para a substituição
 */

require_once 'editor_dinamico.php';

// Simula ID de proposta (pegue um ID real do usuário se possível, ou use um fixo)
// O usuário mencionou "Serviço Alvo: Drone (ID: 19)" no log anterior.
// Preciso de um ID de PROPOSTA que use esse serviço.
// Vou tentar pegar a última proposta criada.

$conn = Database::getProd();
$res = $conn->query("SELECT id_proposta FROM Propostas ORDER BY id_proposta DESC LIMIT 1");
$row = $res->fetch_assoc();
$id_prop = $row['id_proposta'] ?? 0;

if (!$id_prop) die("Nenhuma proposta encontrada.");

echo "<h1>Debug Variáveis - Proposta #$id_prop</h1>";

// Carrega dados (Lógica copiada do editor)
$sql = "SELECT p.*, c.nome_cliente, c.email as email_salvo, c.telefone as telefone_salvo, 
        c.celular as celular_salvo, ts.nome as nome_servico 
        FROM Propostas p 
        LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente 
        LEFT JOIN Tipo_Servicos ts ON p.id_servico = ts.id_servico
        WHERE p.id_proposta = $id_prop";
$data = $conn->query($sql)->fetch_assoc();

// Chama a função corrigida
$vars = getVariableMap($data, $conn);

echo "<pre>";
print_r($vars);
echo "</pre>";

// Teste de Substituição com texto de Drone
$textoTeste = "Terreno: \${TipoTerreno} | Vegetação: \${CoberturaVegetal} | Empresa: \${Empresa}";
echo "<h3>Teste de Substituição:</h3>";
echo "Original: $textoTeste<br>";
echo "Processado: " . substituirVariaveis($textoTeste, $vars);
?>
