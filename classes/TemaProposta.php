<?php
/**
 * Helper para detecção e aplicação de temas de proposta
 * SGT Propostas - Sistema de Temas Legado vs Moderno
 */

class TemaProposta {
    
    // Data da mudança para o novo editor (HOJE)
    const DATA_MUDANCA_LAYOUT = '2026-02-15 00:00:00';
    
    /**
     * Detecta qual tema usar baseado na data de criação
     */
    public static function detectar($dataCriacao) {
        if (empty($dataCriacao)) {
            return 'moderna'; // Padrão seguro para novas
        }
        
        $timestampProposta = strtotime($dataCriacao);
        $timestampMudanca = strtotime(self::DATA_MUDANCA_LAYOUT);
        
        // Se a proposta for ANTERIOR à mudança, usa tema clássico
        return ($timestampProposta < $timestampMudanca) ? 'classica' : 'moderna';
    }
    
    /**
     * Retorna o nome da classe CSS
     */
    public static function getClasse($dataCriacao) {
        return 'tema-' . self::detectar($dataCriacao);
    }
}
?>
