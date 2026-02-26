<?php
require 'db.php';
$id = 249;
$q = $conn->query("SELECT modelo_docx, docx_blocos_count, docx_conteudo FROM Propostas WHERE id_proposta=$id");
$row = $q->fetch_assoc();

header('Content-Type: text/plain; charset=utf-8');
echo "Modelo: " . $row['modelo_docx'] . "\n";
echo "Count: " . $row['docx_blocos_count'] . "\n";
echo "Conteudo: \n";
print_r(json_decode($row['docx_conteudo'], true));
echo "\n\nRaw:\n" . $row['docx_conteudo'];
