<?php
// editar_proposta.php
session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$id_proposta = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$id_usuario = $_SESSION['usuario_id'];

// Lógica de Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novo_status = $_POST['status']; // 'elaborando', 'enviada', 'aceita', 'cancelada'
    
    // ... (outros campos de edição)
    
    $stmt = $conn->prepare("UPDATE Propostas SET status = ? WHERE id_proposta = ? AND id_criador = ?");
    $stmt->bind_param('sii', $novo_status, $id_proposta, $id_usuario);
    
    if ($stmt->execute()) {
        header("Location: painel.php?msg=sucesso");
        exit;
    }
}

// Busca dados para o formulário
$stmt = $conn->prepare("SELECT * FROM Propostas WHERE id_proposta = ? AND id_criador = ?");
$stmt->bind_param('ii', $id_proposta, $id_usuario);
$stmt->execute();
$proposta = $stmt->get_result()->fetch_assoc();
?>

<!-- No seu formulário HTML, a parte do Status deve ser assim: -->
<label class="fw-bold">Status da Proposta</label>
<select name="status" class="form-select shadow-sm border-2">
    <option value="elaborando" <?= ($proposta['status'] == 'elaborando') ? 'selected' : '' ?>>📝 Em Elaboração</option>
    <option value="enviada"    <?= ($proposta['status'] == 'enviada') ? 'selected' : '' ?>>🚀 Enviada ao Cliente</option>
    <option value="aceita"     <?= ($proposta['status'] == 'aceita') ? 'selected' : '' ?>>✅ Aceita (Fechada)</option>
    <option value="cancelada"  <?= ($proposta['status'] == 'cancelada') ? 'selected' : '' ?>>❌ Cancelada/Perdida</option>
</select>