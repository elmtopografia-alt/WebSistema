<?php
/**
 * Script de Teste Rápido
 * Popula os campos novos para uma proposta específica (ID fixo ou via GET)
 */

require_once 'config.php';
require_once 'db.php'; // Usa o db.php para conexão

// ID da proposta de teste (altere aqui ou passe via ?id=X)
$id_proposta = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Tenta pegar o último ID se não informado
if ($id_proposta === 0) {
    echo "<h1>Nenhum ID informado! Buscando última proposta...</h1>";
    $res = $conn->query("SELECT id_proposta FROM Propostas ORDER BY id_proposta DESC LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) {
        $id_proposta = $row['id_proposta'];
        echo "<p>Encontrado ID: <strong>$id_proposta</strong></p>";
    } else {
        die("<h1>Nenhuma proposta encontrada no banco. Crie uma primeira!</h1>");
    }
}

$conn = Database::getProd();

// Dados de Exemplo (Fictícios, mas realistas)
$marca_veic = "Toyota";
$mod_veic = "Hilux 4x4";
$marca_est = "Leica";
$mod_est = "TS06 Plus 2\"";
$marca_gps = "Trimble"; 
$mod_gps = "R12i (RTK/GNSS)";
$marca_drone = "DJI Enterprise";
$mod_drone = "Mavic 3 Multispectral";

$sql = "UPDATE Propostas SET 
    marca_veiculo = ?, modelo_veiculo = ?,
    marca_estacao_total = ?, modelo_estacao_total = ?,
    marca_gps = ?, modelo_gps = ?,
    marca_drone = ?, modelo_drone = ?
    WHERE id_proposta = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssi", 
    $marca_veic, $mod_veic,
    $marca_est, $mod_est,
    $marca_gps, $mod_gps,
    $marca_drone, $mod_drone,
    $id_proposta
);

if ($stmt->execute()) {
    echo "<h1>Sucesso!</h1>";
    echo "<p>Dados de equipamentos inseridos na proposta #$id_proposta.</p>";
    echo "<p><a href='gerar_proposta.php?id=$id_proposta' target='_blank'>Clique aqui para ver a NOVA PROPOSTA gerada</a></p>";
} else {
    echo "Erro ao atualizar: " . $stmt->error;
}
?>
