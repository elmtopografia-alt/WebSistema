<?php
// Arquivo: core.php
require_once 'session_validator.php';
require_once 'config.php';

/*
|--------------------------------------------------------------------------
| VALIDADO PELO session_validator.php
|--------------------------------------------------------------------------
*/
// A validação já ocorreu no include acima.
// O validator garante que $_SESSION['id_usuario'] e $_SESSION['usuario_id'] existam.


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
