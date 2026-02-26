<?php
require_once 'db.php';
$conn = Database::getProd();
$res = $conn->query("SELECT usuario, tipo_perfil, ambiente FROM Usuarios LIMIT 10");
echo "LISTA DE USUARIOS:\n";
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
