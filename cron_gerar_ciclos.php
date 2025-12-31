<?php
//cron_gerar_ciclos.php
require_once 'config.php';

$competencia = date('Y-m');

$sql = "
INSERT INTO Ciclos_Financeiros (id_assinatura, competencia, valor_previsto)
SELECT 
    a.id_assinatura,
    :competencia,
    a.valor_mensal
FROM Assinaturas a
WHERE a.status = 'ativa'
AND NOT EXISTS (
    SELECT 1 FROM Ciclos_Financeiros c
    WHERE c.id_assinatura = a.id_assinatura
    AND c.competencia = :competencia
)
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['competencia' => $competencia]);
