<?php
/**
 * financeiro_calculadora.php
 * CÉREBRO DO DINHEIRO - Centraliza TODOS os cálculos.
 * Regra: Nunca recebe $_POST, apenas arrays/valores. Retorna float.
 */

class FinanceiroCalculadora {

    /**
     * Calcula o valor final da mensalidade com base no plano e periodicidade.
     */
    public static function calcularMensalidade(float $valorBase, string $periodicidade): float {
        $fator = 1.0;
        switch ($periodicidade) {
            case 'trimestral': $fator = 3 * 0.95; break; // 5% desconto
            case 'semestral':  $fator = 6 * 0.90; break; // 10% desconto
            case 'anual':      $fator = 12 * 0.80; break; // 20% desconto
            default:           $fator = 1.0; break; // Mensal
        }
        return round($valorBase * $fator, 2);
    }

    /**
     * Calcula juros e multa (se houver).
     * Por enquanto retorna 0, mas centraliza a lógica.
     */
    public static function calcularJurosMulta(float $valorOriginal, int $diasAtraso): float {
        if ($diasAtraso <= 0) return 0.0;
        
        $multa = $valorOriginal * 0.02; // 2% multa
        $juros = ($valorOriginal * 0.0033) * $diasAtraso; // 1% ao mês pro rata dia
        
        return round($multa + $juros, 2);
    }

    /**
     * Calcula o total a pagar (Valor + Juros/Multa).
     */
    public static function calcularTotalPagar(float $valorOriginal, int $diasAtraso): float {
        $acrescimos = self::calcularJurosMulta($valorOriginal, $diasAtraso);
        return round($valorOriginal + $acrescimos, 2);
    }
}
?>
