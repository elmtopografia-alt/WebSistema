<?php
/**
 * FinanceiroCron.php
 * Tarefas agendadas (Cron Jobs)
 */

require_once __DIR__ . '/FinanceiroRepository.php';

class FinanceiroCron {

    private $repo;

    public function __construct() {
        $this->repo = new FinanceiroRepository();
    }

    /**
     * Gera ciclos financeiros para o próximo mês
     */
    public function gerarCiclos() {
        // Lógica:
        // 1. Buscar todas assinaturas ativas
        // 2. Verificar se já existe ciclo para o mês seguinte
        // 3. Se não, criar
        
        // Exemplo simplificado (Pseudo-código funcional)
        /*
        $assinaturas = $this->repo->buscarTodasAssinaturasAtivas();
        foreach ($assinaturas as $ass) {
            $proxCompetencia = date('Y-m', strtotime('+1 month'));
            if (!$this->repo->existeCiclo($ass['id_assinatura'], $proxCompetencia)) {
                $this->repo->criarCiclo($ass['id_assinatura'], $proxCompetencia, $ass['valor_mensal']);
            }
        }
        */
        return "Ciclos gerados (Simulação)";
    }

    /**
     * Suspende usuários com faturas em atraso > X dias
     */
    public function suspenderInadimplentes() {
        // Lógica:
        // 1. Buscar ciclos pendentes com data de vencimento < (hoje - 5 dias)
        // 2. Alterar status da assinatura para 'suspensa'
        
        return "Inadimplentes verificados (Simulação)";
    }
}
?>
