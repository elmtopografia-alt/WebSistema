<?php
require_once 'db.php';
$res = $conn->query("SELECT id, id_criador, Empresa, logo_url, logo_empresa FROM DadosEmpresa");
echo "<pre>";
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
echo "</pre>";
?>
