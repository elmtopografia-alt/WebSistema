<?php
// Arquivo: core.php
session_start();
require_once 'config.php';

/*
|--------------------------------------------------------------------------
| VERIFICA LOGIN
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$id_usuario = intval($_SESSION['id_usuario']);

/*
|--------------------------------------------------------------------------
| CARREGA IDENTIDADE DO USUÁRIO (DNA)
|--------------------------------------------------------------------------
*/
$sql = "
    SELECT 
        id_usuario,
        tipo_perfil,
        ambiente
    FROM Usuarios
    WHERE id_usuario = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

if (!$usuario) {
    session_destroy();
    header('Location: login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| CONSTANTES DO DNA
|--------------------------------------------------------------------------
*/
define('USUARIO_ID', $usuario['id_usuario']);
define('USUARIO_PERFIL', $usuario['tipo_perfil']); // admin | cliente
define('USUARIO_AMBIENTE', $usuario['ambiente']);  // producao | demo

/*
|--------------------------------------------------------------------------
| FUNÇÕES DO CÉREBRO
|--------------------------------------------------------------------------
*/
function exigeAdmin() {
    if (USUARIO_PERFIL !== 'admin') {
        http_response_code(403);
        die('acesso negado');
    }
}

function exigeCliente() {
    if (USUARIO_PERFIL !== 'cliente') {
        http_response_code(403);
        die('acesso restrito');
    }
}

function exigeAmbiente($ambiente) {
    if (USUARIO_AMBIENTE !== $ambiente) {
        http_response_code(403);
        die('ambiente inválido');
    }
}

/*
|--------------------------------------------------------------------------
| REGRA DE OURO DO DEMO
|--------------------------------------------------------------------------
*/
function bloqueiaDemoParaFinanceiro() {
    if (USUARIO_AMBIENTE === 'demo') {
        die('funcionalidade indisponível no ambiente demo');
    }
}
