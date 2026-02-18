<?php
/**
 * GERADOR DE PROPOSTA PREMIUM (NOVO MOTOR)
 * Ponte simples que redireciona para o módulo isolado em
 * `testes_isolados/crm-propostas/gerar-proposta.php`.
 *
 * Uso:
 *   gerar_proposta_premium.php?id=98
 *   gerar_proposta_premium.php?id=98&tema=drone
 */

require_once 'session_validator.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Garante que o usuário esteja autenticado (mesma lógica do restante do sistema)
if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo "ID da proposta inválido.";
    exit;
}

// Tema opcional (drone, classico, moderno). Se não vier, o módulo usa detecção automática.
$tema = isset($_GET['tema']) ? trim($_GET['tema']) : '';

$base = 'testes_isolados/crm-propostas/gerar-proposta.php';
$query = "id={$id}";

if ($tema !== '') {
    $query .= '&tema=' . urlencode($tema);
}

$url = $base . '?' . $query;

header("Location: {$url}");
exit;

