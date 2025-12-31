$pdo->beginTransaction();
// registrar_pagamento.php
// 1. Registra pagamento
$sql = "INSERT INTO Pagamentos 
(id_ciclo, valor_pago, data_pagamento, metodo)
VALUES (?, ?, NOW(), ?)";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_ciclo, $valor, $metodo]);

$id_pagamento = $pdo->lastInsertId();

// 2. Gera número do recibo
$numero_recibo = 'REC-' . date('Ymd') . '-' . str_pad($id_pagamento, 6, '0', STR_PAD_LEFT);

// 3. Cria recibo
$sql = "INSERT INTO Recibos
(id_pagamento, numero_recibo, emissor_nome, emissor_cnpj)
VALUES (?, ?, ?, ?)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $id_pagamento,
    $numero_recibo,
    'ELM Serviços Topográficos Ltda',
    'CNPJ_DA_ELM_AQUI'
]);

$pdo->commit();
