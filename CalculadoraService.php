<?php
/**
 * CalculadoraService.php
 * Único lugar onde cálculos financeiros acontecem
 * Versão: 1.0 | 2026-02-12
 */

class CalculadoraService 
{
    public function calcularSalarios($qtd, $base, $encargos, $dias) 
    {
        return ($qtd * $base * (1 + $encargos / 100) / 30) * $dias;
    }
    
    public function calcularEstadia($qtd, $valor, $dias) 
    {
        return $qtd * $valor * $dias;
    }
    
    public function calcularConsumos($qtd, $kml, $valorLitro, $kmTotal) 
    {
        if ($kml <= 0) return 0;
        return ($kmTotal * $valorLitro / $kml) * $qtd;
    }
    
    public function calcularLocacao($qtd, $valorMensal, $dias) 
    {
        return ($qtd * $valorMensal / 30) * $dias;
    }
    
    public function calcularAdmin($qtd, $valor) 
    {
        return $qtd * $valor;
    }
    
    public function fecharProposta($custoOperacional, $percentualLucro, $desconto) 
    {
        $valorLucro = $custoOperacional * ($percentualLucro / 100);
        $subtotal = $custoOperacional + $valorLucro;
        $valorFinal = $subtotal - $desconto;
        
        return [
            'custo_operacional' => $custoOperacional,
            'valor_lucro' => $valorLucro,
            'subtotal' => $subtotal,
            'valor_final' => $valorFinal
        ];
    }
    
    public function calcularMobilizacao($valorFinal, $percentualMobilizacao) 
    {
        $mobilizacao = $valorFinal * ($percentualMobilizacao / 100);
        return [
            'mobilizacao_valor' => $mobilizacao,
            'restante_valor' => $valorFinal - $mobilizacao,
            'restante_percentual' => 100 - $percentualMobilizacao
        ];
    }
    
    public function valorPorExtenso($valor) 
    {
        $valor = round($valor, 2);
        if (!class_exists('NumberFormatter')) {
            return number_format($valor, 2, ',', '.') . ' reais';
        }
        $fmt = new NumberFormatter('pt_BR', NumberFormatter::SPELLOUT);
        return $fmt->format($valor) . ' reais';
    }
}
