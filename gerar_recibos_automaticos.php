<?php
// Arquivo: gerar_recibos_automaticos.php

require_once __DIR__ . '/../config/config.php';

$emissor_nome = 'elm serviços topográficos ltda';
$emissor_cnpj = 'ELM Serviços Topográficos Ltda';

$sql = "
    SELECT p.id_pagamento
    FROM Pagamentos p
    LEFT JOIN Recibos r ON r.id_pagamento = p.id_pagamento
    WHERE r.id_recibo IS NULL
";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {

    $id_pagamento = $row['id_pagamento'];
    $numero_recibo = 'R' . date('YmdHis') . $id_pagamento;

    $stmt = $conn->prepare("
        INSERT INTO Recibos
        (id_pagamento, numero_recibo, emissor_nome, emissor_cnpj)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "isss",
        $id_pagamento,
        $numero_recibo,
        $emissor_nome,
        $emissor_cnpj
    );

    $stmt->execute();
}

file_put_contents(
    __DIR__ . '/../logs/cron.log',
    date('Y-m-d H:i:s') . " - recibos automáticos gerados\n",
    FILE_APPEND
);
