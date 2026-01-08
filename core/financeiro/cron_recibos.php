<?php
/**
 * cron_recibos.php
 * Automação - Garante geração de recibos.
 * Regra: Corrige falhas automáticas.
 */

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/gerar_recibo.php';

// Apenas CLI ou proteção
if (php_sapi_name() !== 'cli' && !isset($_GET['token_seguranca'])) {
    die('Acesso negado');
}

$conn = Database::getProd();
$gerador = new GerarRecibo();

echo "Verificando pagamentos sem recibo...\n";

// Busca pagamentos confirmados que não têm recibo na tabela Recibos
$sql = "SELECT p.id_pagamento 
        FROM Pagamentos p 
        LEFT JOIN Recibos r ON p.id_pagamento = r.id_pagamento 
        WHERE r.id_recibo IS NULL";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    if ($gerador->gerar($row['id_pagamento'])) {
        echo "Recibo gerado para Pagamento ID {$row['id_pagamento']}\n";
    } else {
        echo "Erro ao gerar recibo para Pagamento ID {$row['id_pagamento']}\n";
    }
}

echo "Concluido.\n";
?>
