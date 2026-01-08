<?php
/**
 * gerar_recibo.php
 * Persistência Financeira - Gera recibo oficial.
 * Regra: Texto minúsculo, emissor fixo. Nunca recebe valor direto.
 */

require_once __DIR__ . '/../../db.php';

class GerarRecibo {

    private $conn;

    public function __construct() {
        $isDemo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');
        $this->conn = $isDemo ? Database::getDemo() : Database::getProd();
    }

    public function gerar(int $idPagamento) {
        // 1. Busca dados do pagamento (Segurança: só usa dados do banco)
        $sql = "SELECT p.id_pagamento, p.valor_pago, p.data_pagamento, c.competencia, u.nome_completo 
                FROM Pagamentos p
                JOIN Ciclos_Financeiros c ON p.id_ciclo = c.id_ciclo
                JOIN Assinaturas a ON c.id_assinatura = a.id_assinatura
                JOIN Usuarios u ON a.id_usuario = u.id_usuario
                WHERE p.id_pagamento = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idPagamento);
        $stmt->execute();
        $pagamento = $stmt->get_result()->fetch_assoc();

        if (!$pagamento) return false;

        // 2. Gera número do recibo
        $numeroRecibo = date('Ymd') . str_pad($idPagamento, 6, '0', STR_PAD_LEFT);

        // 3. Dados fixos do emissor
        $emissorNome = "elm serviços topográficos ltda";
        $emissorCnpj = "00.000.000/0001-00"; // Exemplo

        // 4. Salva Recibo
        $sqlInsert = "INSERT INTO Recibos (id_pagamento, numero_recibo, emissor_nome, emissor_cnpj, data_emissao) VALUES (?, ?, ?, ?, NOW())";
        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->bind_param("isss", $idPagamento, $numeroRecibo, $emissorNome, $emissorCnpj);
        
        return $stmtInsert->execute();
    }
}
?>
