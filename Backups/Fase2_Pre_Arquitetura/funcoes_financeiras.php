<?php
//funcoes_financeiras.php
if (!defined('FINANCEIRO')) {
    die('Acesso direto não permitido');
}

function gerarNumeroRecibo($idPagamento) {
    return 'REC-' . date('Y') . '-' . str_pad($idPagamento, 6, '0', STR_PAD_LEFT);
}

function obterEmissor($conn) {
    $sql = "SELECT Empresa, CNPJ FROM DadosEmpresa ORDER BY id_empresa ASC LIMIT 1";
    $res = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($res);
}
