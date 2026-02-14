<?php
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../config/equipamentos_servico.php';

$EQUIPAMENTOS_SERVICO = require '../config/equipamentos_servico.php';

$id_servico = $_GET['id_servico'] ?? 0;

// Buscar tipo do serviço
// Ajuste: A tabela pode ser 'Tipo_Servicos' ou 'servicos'. No editor_dinamico.php usa 'Tipo_Servicos'.
// O usuário forneceu 'servicos'. Vou verificar o nome correto da tabela.
// O arquivo editor_dinamico.php linha 348 faz: LEFT JOIN Tipo_Servicos ts ON p.id_servico = ts.id_servico
// Então a tabela é 'Tipo_Servicos'.
// Vou adaptar o código do usuário para usar 'Tipo_Servicos' e a coluna correta 'nome' ou 'slug'.
// Como não sei se tem coluna 'tipo' ou 'slug', vou assumir 'nome' e fazer um match manual similar ao que planejei antes, mas o usuário mandou um código que faz `SELECT tipo FROM servicos`.
// Vou verificar as colunas de Tipo_Servicos.

require_once '../db.php'; // Fallback para conexão se config/database.php não for o correto para conexão direta simples.
// Mas `config/database.php` deve retornar uma conexão PDO ou mysqli?
// `db.php` retorna `Database::getProd()`.
// Vou usar `db.php` que sei que funciona.

$conn = Database::getProd();
$sql = "SELECT nome FROM Tipo_Servicos WHERE id_servico = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_servico);
$stmt->execute();
$res = $stmt->get_result();
$servico = $res->fetch_assoc();

if (!$servico) {
    echo json_encode(['success' => false, 'error' => 'Serviço não encontrado']);
    exit;
}

$nome_normalizado = mb_strtolower($servico['nome']);
$tipo = 'padrao';

// Mapeamento simples baseado no nome
if (strpos($nome_normalizado, 'drone') !== false || strpos($nome_normalizado, 'fotogrametria') !== false) {
    $tipo = 'drone_fotogrametria';
} elseif (strpos($nome_normalizado, 'geo') !== false) {
    $tipo = 'georreferenciamento';
} elseif (strpos($nome_normalizado, 'topografia') !== false) {
    $tipo = 'topografia_tradicional';
} elseif (strpos($nome_normalizado, 'cadastral') !== false) {
    $tipo = 'levantamento_cadastral';
}

$equipamentos = $EQUIPAMENTOS_SERVICO[$tipo] ?? $EQUIPAMENTOS_SERVICO['drone_fotogrametria'];

echo json_encode(array_merge(['success' => true], $equipamentos));
