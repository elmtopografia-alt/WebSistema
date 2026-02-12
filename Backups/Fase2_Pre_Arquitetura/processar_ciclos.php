<?php
/**
 * Aruivo: processar_ciclos.php
 * cron/processar_ciclos.php
 * Verifica ciclos financeiros vencidos e suspende acessos
 */

require_once __DIR__ . '/../config.php';

$data_hoje = date('Y-m-d');

try {
    // ==============================
    // Buscar ciclos vencidos e não pagos
    // ==============================
    $sql = "
        SELECT 
            c.id_ciclo,
            a.id_usuario
        FROM Ciclos_Financeiros c
        JOIN Assinaturas a ON a.id_assinatura = c.id_assinatura
        WHERE 
            c.status = 'aberto'
            AND c.data_vencimento < ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data_hoje]);
    $ciclos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$ciclos) {
        registrarLog('Nenhum ciclo vencido.');
        exit;
    }

    foreach ($ciclos as $ciclo) {

        // Suspende usuário
        $sql = "
            UPDATE Usuarios
            SET tipo_perfil = 'suspenso_financeiro'
            WHERE id_usuario = ?
            AND tipo_perfil <> 'admin'
        ";

        $pdo->prepare($sql)->execute([$ciclo['id_usuario']]);

        // Atualiza status do ciclo
        $sql = "
            UPDATE Ciclos_Financeiros
            SET status = 'vencido'
            WHERE id_ciclo = ?
        ";

        $pdo->prepare($sql)->execute([$ciclo['id_ciclo']]);

        registrarLog(
            'Usuário ' . $ciclo['id_usuario'] . 
            ' suspenso por ciclo ' . $ciclo['id_ciclo']
        );
    }

} catch (Exception $e) {
    registrarLog('ERRO: ' . $e->getMessage());
}

// ==============================
// Função de log
// ==============================
function registrarLog($mensagem)
{
    $linha = '[' . date('Y-m-d H:i:s') . '] ' . $mensagem . PHP_EOL;
    file_put_contents(
        __DIR__ . '/log_cron.txt',
        $linha,
        FILE_APPEND
    );
}
