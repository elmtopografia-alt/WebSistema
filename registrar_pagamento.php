<?php
/**
 * registrar_pagamento.php
 * Registra um pagamento financeiro, gera recibo automaticamente
 * e reativa o acesso do usuário.
 */

require_once 'config.php';
session_start();

// ==============================
// Validação de entrada mínima
// ==============================
if (
    empty($_POST['id_ciclo']) ||
    empty($_POST['valor_pago'])
) {
    http_response_code(400);
    echo 'Dados obrigatórios ausentes.';
    exit;
}

$id_ciclo   = (int) $_POST['id_ciclo'];
$valor_pago = (float) $_POST['valor_pago'];
$metodo     = isset($_POST['metodo']) ? $_POST['metodo'] : 'manual';

try {
    $pdo->beginTransaction();

    // ==============================
    // 1. Valida ciclo financeiro
    // ==============================
    $sql = "SELECT c.id_ciclo, c.status, a.id_usuario
            FROM Ciclos_Financeiros c
            JOIN Assinaturas a ON a.id_assinatura = c.id_assinatura
            WHERE c.id_ciclo = ? FOR UPDATE";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_ciclo]);
    $ciclo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ciclo) {
        throw new Exception('Ciclo financeiro inexistente.');
    }

    if ($ciclo['status'] === 'pago') {
        throw new Exception('Este ciclo já está quitado.');
    }

    // ==============================
    // 2. Registra pagamento
    // ==============================
    $sql = "INSERT INTO Pagamentos
            (id_ciclo, valor_pago, data_pagamento, metodo)
            VALUES (?, ?, NOW(), ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_ciclo, $valor_pago, $metodo]);

    $id_pagamento = $pdo->lastInsertId();

    // ==============================
    // 3. Gera número do recibo
    // ==============================
    $numero_recibo = 'REC-' . date('Ymd') . '-' .
                     str_pad($id_pagamento, 6, '0', STR_PAD_LEFT);

    // ==============================
    // 4. Cria recibo
    // ==============================
    $sql = "INSERT INTO Recibos
            (id_pagamento, numero_recibo, emissor_nome, emissor_cnpj)
            VALUES (?, ?, ?, ?)";

    require_once 'config_empresa.php';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $id_pagamento,
        $numero_recibo,
        EMPRESA_NOME,
        EMPRESA_CNPJ
    ]);

    // ==============================
    // 5. Marca ciclo como pago
    // ==============================
    $sql = "UPDATE Ciclos_Financeiros
            SET status = 'pago'
            WHERE id_ciclo = ?";

    $pdo->prepare($sql)->execute([$id_ciclo]);

    // ==============================
    // 6. Reativa usuário
    // ==============================
    $sql = "UPDATE Usuarios
            SET tipo_perfil = 'ativo'
            WHERE id_usuario = ?";

    $pdo->prepare($sql)->execute([$ciclo['id_usuario']]);

    $pdo->commit();

    echo 'Pagamento registrado com sucesso. Recibo: ' . $numero_recibo;

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo 'Erro financeiro: ' . $e->getMessage();
}
