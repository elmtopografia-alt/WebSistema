<?php
/**
 * registrar_pagamento.php
 * Persistência Financeira - Grava o pagamento no banco.
 * Regra: Não calcula valores. Apenas CONSOME o resultado da calculadora.
 */

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/financeiro_calculadora.php';

class RegistrarPagamento {

    private $conn;

    public function __construct() {
        // Detecta ambiente ou usa produção
        $isDemo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');
        $this->conn = $isDemo ? Database::getDemo() : Database::getProd();
    }

    public function registrar(int $idCiclo, float $valorPago, string $metodo, string $comprovante = null) {
        // 1. Prepara SQL
        $sql = "INSERT INTO Pagamentos (id_ciclo, valor_pago, data_pagamento, metodo, comprovante) VALUES (?, ?, NOW(), ?, ?)";
        $stmt = $this->conn->prepare($sql);
        
        // 2. Executa
        $stmt->bind_param("idss", $idCiclo, $valorPago, $metodo, $comprovante);
        
        if ($stmt->execute()) {
            $idPagamento = $stmt->insert_id;
            
            // 3. Atualiza status do ciclo
            $this->atualizarCiclo($idCiclo, 'pago');
            
            // 4. Dispara geração de recibo (automático)
            $this->dispararRecibo($idPagamento);
            
            return $idPagamento;
        }
        return false;
    }

    private function atualizarCiclo($idCiclo, $status) {
        $stmt = $this->conn->prepare("UPDATE Ciclos_Financeiros SET status = ? WHERE id_ciclo = ?");
        $stmt->bind_param("si", $status, $idCiclo);
        $stmt->execute();
    }

    private function dispararRecibo($idPagamento) {
        require_once __DIR__ . '/gerar_recibo.php';
        $gerador = new GerarRecibo();
        $gerador->gerar($idPagamento);
    }
}
?>
