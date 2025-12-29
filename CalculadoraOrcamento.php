<?php
/**
 * CalculadoraOrcamento.php
 * Motor de Cálculo - Espelho fiel do calculos.js
 * 
 * Responsabilidade:
 * Reproduzir no servidor a exata lógica matemática que o usuário vê na tela.
 */

class CalculadoraOrcamento {

    /**
     * Calcula Custo de Salários (Mão de Obra)
     * JS Logic: (qtd * base * (1 + enc/100) / 30) * dias
     */
    public function calcularSalarios($qtd, $salarioBase, $encargosPercentual, $dias) {
        $qtd = floatval($qtd);
        $salarioBase = floatval($salarioBase);
        $encargosPercentual = floatval($encargosPercentual);
        $dias = floatval($dias);

        // Proteção: Encargos vazios assumem 0
        $fatorEncargos = 1 + ($encargosPercentual / 100);
        
        // Cálculo
        $total = ($qtd * $salarioBase * $fatorEncargos / 30) * $dias;
        
        return $total;
    }

    /**
     * Calcula Custo de Estadia
     * JS Logic: qtd * val * dias
     */
    public function calcularEstadia($qtd, $valorUnitario, $dias) {
        $qtd = floatval($qtd);
        $valorUnitario = floatval($valorUnitario);
        $dias = floatval($dias);

        return $qtd * $valorUnitario * $dias;
    }

    /**
     * Calcula Custo de Consumos (Combustível)
     * JS Logic: (kml > 0) ? (kmt * lit / kml) * qtd : 0
     */
    public function calcularConsumos($qtd, $kml, $valorLitro, $kmTotal) {
        $qtd = floatval($qtd);
        $kml = floatval($kml);
        $valorLitro = floatval($valorLitro);
        $kmTotal = floatval($kmTotal);

        if ($kml <= 0) return 0.00;

        return ($kmTotal * $valorLitro / $kml) * $qtd;
    }

    /**
     * Calcula Custo de Locação
     * JS Logic: (qtd * val / 30) * dias
     */
    public function calcularLocacao($qtd, $valorMensal, $dias) {
        $qtd = floatval($qtd);
        $valorMensal = floatval($valorMensal);
        $dias = floatval($dias);

        return ($qtd * $valorMensal / 30) * $dias;
    }

    /**
     * Calcula Custos Administrativos
     * JS Logic: qtd * val
     */
    public function calcularAdmin($qtd, $valorUnitario) {
        $qtd = floatval($qtd);
        $valorUnitario = floatval($valorUnitario);

        return $qtd * $valorUnitario;
    }

    /**
     * Fecha o Preço Final (Lucro, Desconto, Totais)
     * 
     * @param float $custoTotalGeral (Soma de tudo acima)
     * @param float $percentualLucro
     * @param float $valorDesconto
     */
    public function fecharProposta($custoTotalGeral, $percentualLucro, $valorDesconto) {
        $custoTotalGeral = floatval($custoTotalGeral);
        $percentualLucro = floatval($percentualLucro);
        $valorDesconto = floatval($valorDesconto);

        // 1. Calcula Valor do Lucro
        // JS: geral * (lucroPerc/100)
        $valorLucro = $custoTotalGeral * ($percentualLucro / 100);

        // 2. Calcula Subtotal
        // JS: geral + lucroValor
        $subtotal = $custoTotalGeral + $valorLucro;

        // 3. Calcula Final
        // JS: subtotal - desconto
        $valorFinal = $subtotal - $valorDesconto;

        return [
            'custo_total' => round($custoTotalGeral, 2),
            'valor_lucro' => round($valorLucro, 2),
            'subtotal'    => round($subtotal, 2),
            'valor_final' => round($valorFinal, 2)
        ];
    }
}
?>