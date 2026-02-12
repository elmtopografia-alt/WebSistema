<?php
// atualizar_status.php
session_start();
require 'db.php';

if (!isset($_GET['id']) || !isset($_GET['status'])) {
    header("Location: listar_propostas.php");
    exit();
}

$id = (int)$_GET['id'];
$status = $_GET['status'];
$stmt = $conn->prepare("UPDATE Propostas SET status = ? WHERE id_proposta = ?");
$stmt->bind_param('si', $status, $id);
$stmt->execute();

header("Location: listar_propostas.php");
exit();
?>