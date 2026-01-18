<?php
//cron_suspender_inadimplentes.php
require_once 'config.php';
require_once 'database.php';

$sql = "
UPDATE Usuarios u
JOIN Assinaturas a ON a.id_usuario = u.id_usuario
JOIN Ciclos_Financeiros c ON c.id_assinatura = a.id_assinatura
SET u.tipo_perfil = 'suspenso_financeiro'
WHERE c.status = 'em_atraso'
AND a.status = 'ativa'
AND u.ambiente = 'producao'
";

$pdo->exec($sql);
