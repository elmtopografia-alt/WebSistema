<?php
/**
 * SolarCalculator.php
 * Motor de Cálculo Fotovoltaico - Backend Seguro.
 * 
 * Responsabilidade:
 * Realizar todos os cálculos matemáticos (Dimensionamento, Geração e Financeiro)
 * no servidor, impedindo manipulação via Front-end.
 */

// Garante acesso ao banco de dados (se precisarmos buscar irradiação no futuro)
require_once __DIR__ . '/db.php';

class SolarCalculator {

    // Constantes do Mercado (Podem vir do banco no futuro)
    const PERDA_SISTEMA = 0.20; // 20% de perda (sujeira, cabos, temperatura)
    const EFICIENCIA_INVERSOR = 0.97; // 97%
    
    /**
     * Calcula o tamanho do sistema necessário (Potência kWp).
     * 
     * @param float $consumoMensal (kWh) - Ex: 500
     * @param float $irradiacaoSolar (HSP - Horas de Sol Pleno) - Ex: 4.5 (Média da cidade)
     * @return array Detalhes do dimensionamento
     */
    public function dimensionarSistema($consumoMensal, $irradiacaoSolar) {
        
        // 1. Cálculo da Energia Diária Necessária
        $energiaDiaria = $consumoMensal / 30;

        // 2. Performance Ratio (PR) estimado (1 - perdas)
        $performanceRatio = 1 - self::PERDA_SISTEMA;

        // 3. Fórmula: Potência (kWp) = Energia Diária / (Irradiação * PR)
        // Evita divisão por zero
        if ($irradiacaoSolar <= 0) $irradiacaoSolar = 4.5; // Valor médio Brasil se falhar

        $potenciaNecessaria = $energiaDiaria / ($irradiacaoSolar * $performanceRatio);

        // Arredonda para 2 casas decimais
        return round($potenciaNecessaria, 2);
    }

    /**
     * Calcula a Geração Estimada do Sistema proposto.
     * Serve para provar ao cliente quanto ele vai gerar.
     * 
     * @param float $potenciaInstalada (kWp) - O tamanho do sistema (ex: 4.4 kWp)
     * @param float $irradiacaoSolar (HSP)
     * @return float Geração Mensal Média (kWh)
     */
    public function calcularGeracaoMensal($potenciaInstalada, $irradiacaoSolar) {
        $performanceRatio = 1 - self::PERDA_SISTEMA;
        
        // Fórmula: Geração = Potência * Irradiação * 30 dias * PR
        $geracao = $potenciaInstalada * $irradiacaoSolar * 30 * $performanceRatio;
        
        return round($geracao, 0); // Ex: 501 kWh
    }

    /**
     * Calcula a Economia Financeira (R$).
     * 
     * @param float $geracaoMensal (kWh)
     * @param float $tarifaEnergia (R$/kWh) - Ex: 0.95
     * @return float Valor economizado no primeiro mês
     */
    public function calcularEconomiaReais($geracaoMensal, $tarifaEnergia) {
        return round($geracaoMensal * $tarifaEnergia, 2);
    }

    /**
     * Simulação Financeira Simplificada (Payback Simples).
     * 
     * @param float $custoTotalSistema (R$) - Ex: 15000.00
     * @param float $economiaMensal (R$) - Ex: 500.00
     * @return float Meses para o retorno
     */
    public function calcularPaybackMeses($custoTotalSistema, $economiaMensal) {
        if ($economiaMensal <= 0) return 0;
        return round($custoTotalSistema / $economiaMensal, 1);
    }
}
?>