<?php
session_start();
header('Content-Type: text/plain');
echo "--- DIAGNÓSTICO DE SESSÃO ---\n";
echo "Session ID: " . session_id() . "\n";
echo "Usuario ID: " . ($_SESSION['usuario_id'] ?? 'NÃO DEFINIDO') . "\n";
echo "Nome: " . ($_SESSION['user_name'] ?? 'NÃO DEFINIDO') . "\n";
echo "Cookie Params: " . print_r(session_get_cookie_params(), true) . "\n";
?>
