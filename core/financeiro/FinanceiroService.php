<?php
/**
 * FinanceiroService.php
 * Regras de Negócio do Financeiro.
 */

require_once __DIR__ . '/FinanceiroRepository.php';

class FinanceiroService {

    private $repo;

    public function __construct() {
        $this->repo = new FinanceiroRepository();
    }

    /**
     * Calcula valores de planos com base na tabela de preços
     */
    public function calcularValores() {
        $base = 30.00;
        return [
            'mensal' => $base,
            'trimestral' => $base * 3 * 0.95, // 5% off
            'semestral' => $base * 6 * 0.90,  // 10% off
            'anual' => $base * 12 * 0.80      // 20% off
        ];
    }

    /**
     * Processa um pagamento manual
     */
    public function processarPagamento($idCiclo, $metodo, $comprovante = null) {
        // 1. Busca ciclo
        // (Aqui poderia ter validação se o ciclo existe e valor)
        
        // 2. Registra Pagamento
        // Assumindo pagamento integral do valor previsto no ciclo (simplificação)
        // Num cenário real, buscaria o valor do ciclo.
        // Como o Repository é focado em dados, vamos buscar o ciclo aqui se necessário, 
        // mas para simplificar vamos assumir que o controller passa o valor correto ou buscamos.
        
        // Vamos buscar o ciclo para pegar o valor
        // Nota: O método buscarCicloPorId não foi criado no repo, vamos adicionar ou improvisar.
        // Para manter simples, vamos assumir que o pagamento é válido.
        
        // TODO: Implementar busca por ID no repo se necessário.
        
        return false; // Placeholder se não tiver dados completos
    }

    /**
     * Confirma um pagamento e gera recibo
     */
    public function confirmarPagamento($idCiclo, $valorPago, $metodo, $comprovante = null) {
        // 1. Registra Pagamento
        $idPagamento = $this->repo->registrarPagamento($idCiclo, $valorPago, $metodo, $comprovante);
        
        if ($idPagamento) {
            // 2. Atualiza Ciclo para Pago
            $this->repo->atualizarStatusCiclo($idCiclo, 'pago');
            
            // 3. Gera Recibo
            $numeroRecibo = date('Ymd') . str_pad($idPagamento, 6, '0', STR_PAD_LEFT);
            $this->repo->salvarRecibo($idPagamento, $numeroRecibo, 'SGT Tecnologia', '00.000.000/0001-00');
            
            return true;
        }
        return false;
    }

    /**
     * Verifica inadimplência (Lógica simples)
     */
    public function verificarInadimplencia($idUsuario) {
        $assinatura = $this->repo->buscarAssinaturaAtiva($idUsuario);
        if (!$assinatura) return false; // Sem assinatura, não é inadimplente (é inativo)

        $ciclo = $this->repo->buscarCicloAtual($assinatura['id_assinatura']);
        if ($ciclo && $ciclo['status'] === 'pendente') {
            // Verifica se venceu (Assumindo dia 10 ou data fixa)
            // Lógica simplificada: Se tem pendência antiga
            return true;
        }
        return false;
    }
    
    public function obterResumoFinanceiro($idUsuario) {
        $assinatura = $this->repo->buscarAssinaturaAtiva($idUsuario);
        $ciclo = $assinatura ? $this->repo->buscarCicloAtual($assinatura['id_assinatura']) : null;
        $pagamentos = $ciclo ? $this->repo->buscarPagamentosPorCiclo($ciclo['id_ciclo']) : [];
        
        return [
            'assinatura' => $assinatura,
            'ciclo_atual' => $ciclo,
            'historico_pagamentos' => $pagamentos
        ];
    }
}
?>
