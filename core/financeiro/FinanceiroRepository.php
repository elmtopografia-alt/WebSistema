<?php
/**
 * FinanceiroRepository.php
 * Camada de Acesso a Dados (DAO) para o módulo financeiro.
 */

require_once __DIR__ . '/../../db.php';

class FinanceiroRepository {

    private $conn;

    public function __construct() {
        // Detecta ambiente da sessão (se disponível) ou usa Produção como fallback
        $isDemo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');
        $this->conn = $isDemo ? Database::getDemo() : Database::getProd();
    }

    // --- ASSINATURAS ---
    public function buscarAssinaturaAtiva($idUsuario) {
        $sql = "SELECT * FROM Assinaturas WHERE id_usuario = ? AND status = 'ativa' ORDER BY id_assinatura DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function criarAssinatura($idUsuario, $plano, $valor) {
        $sql = "INSERT INTO Assinaturas (id_usuario, plano, valor_mensal, status, data_inicio) VALUES (?, ?, ?, 'ativa', NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isd", $idUsuario, $plano, $valor);
        return $stmt->execute();
    }

    // --- CICLOS ---
    public function buscarCicloAtual($idAssinatura) {
        $sql = "SELECT * FROM Ciclos_Financeiros WHERE id_assinatura = ? ORDER BY competencia DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idAssinatura);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function criarCiclo($idAssinatura, $competencia, $valor) {
        $sql = "INSERT INTO Ciclos_Financeiros (id_assinatura, competencia, valor_previsto, status) VALUES (?, ?, ?, 'pendente')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isd", $idAssinatura, $competencia, $valor);
        return $stmt->execute();
    }

    public function atualizarStatusCiclo($idCiclo, $status) {
        $sql = "UPDATE Ciclos_Financeiros SET status = ? WHERE id_ciclo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $idCiclo);
        return $stmt->execute();
    }

    // --- PAGAMENTOS ---
    public function registrarPagamento($idCiclo, $valor, $metodo, $comprovante = null) {
        $sql = "INSERT INTO Pagamentos (id_ciclo, valor_pago, data_pagamento, metodo, comprovante) VALUES (?, ?, NOW(), ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("idss", $idCiclo, $valor, $metodo, $comprovante);
        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        return false;
    }

    public function buscarPagamentosPorCiclo($idCiclo) {
        $sql = "SELECT * FROM Pagamentos WHERE id_ciclo = ? ORDER BY data_pagamento DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idCiclo);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // --- RECIBOS ---
    public function salvarRecibo($idPagamento, $numeroRecibo, $emissorNome, $emissorCnpj) {
        $sql = "INSERT INTO Recibos (id_pagamento, numero_recibo, emissor_nome, emissor_cnpj, data_emissao) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $idPagamento, $numeroRecibo, $emissorNome, $emissorCnpj);
        return $stmt->execute();
    }
}
?>
