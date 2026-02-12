<?php
// excluir_proposta.php
session_start();
require 'db.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    // Exclui dependências (Cascade manual se o banco não tiver)
    $conn->query("DELETE FROM Proposta_Salarios WHERE id_proposta = $id");
    $conn->query("DELETE FROM Proposta_Estadia WHERE id_proposta = $id");
    $conn->query("DELETE FROM Proposta_Consumos WHERE id_proposta = $id");
    $conn->query("DELETE FROM Proposta_Locacao WHERE id_proposta = $id");
    $conn->query("DELETE FROM Proposta_Custos_Administrativos WHERE id_proposta = $id");
    
    $conn->query("DELETE FROM Propostas WHERE id_proposta = $id");
}
header("Location: listar_propostas.php");
?>