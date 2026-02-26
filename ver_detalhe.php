<?php
/**
 * VER DETALHE - Alternativa de Debug
 * Acesse: ver_detalhe.php?id=249
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/PropostaRepository.php';

$id_proposta = intval($_GET['id'] ?? 0);
if ($id_proposta <= 0) {
    die("ID inválido");
}

$repo = new PropostaRepository($conn);
$dados = $repo->buscarPorId($id_proposta);

echo "<h1>Dados da Proposta #{$id_proposta}</h1>";
echo "<pre>";
if (!$dados) {
    echo "Proposta não encontrada no repositório.";
} else {
    print_r($dados);

    // Buscar cliente
    if (!empty($dados['id_cliente'])) {
        $stmt = $conn->prepare("SELECT * FROM Clientes WHERE id_cliente = ?");
        $stmt->bind_param("i", $dados['id_cliente']);
        $stmt->execute();
        $cliente = $stmt->get_result()->fetch_assoc();
        echo "<h2>Dados do Cliente:</h2>";
        print_r($cliente);
    }

    // Buscar obra
    if (!empty($dados['id_obra'])) {
        $stmt = $conn->prepare("SELECT * FROM obras WHERE id = ?");
        $stmt->bind_param("i", $dados['id_obra']);
        $stmt->execute();
        $obra = $stmt->get_result()->fetch_assoc();
        echo "<h2>Dados da Obra:</h2>";
        print_r($obra);
    }
}
echo "</pre>";
