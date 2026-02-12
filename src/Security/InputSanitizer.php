<?php
/**
 * Sanitização e Validação de Inputs
 * 
 * Proteção contra XSS, SQL Injection e inputs maliciosos
 * 
 * @package SGT_Propostas
 * @subpackage Security
 */

declare(strict_types=1);

namespace SGT\Security;

class InputSanitizer
{
    /**
     * Sanitiza string para output HTML
     */
    public static function html(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Sanitiza para atributo HTML
     */
    public static function attr(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Remove tags HTML (para campos de texto puro)
     */
    public static function plain(string $text): string
    {
        return strip_tags($text);
    }
    
    /**
     * Sanitiza email
     */
    public static function email(string $email): string
    {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Valida e sanitiza URL
     */
    public static function url(string $url): ?string
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        return filter_var($url, FILTER_VALIDATE_URL) ?: null;
    }
    
    /**
     * Sanitiza inteiro
     */
    public static function int($value): int
    {
        return (int)filter_var($value, FILTER_VALIDATE_INT);
    }
    
    /**
     * Sanitiza float
     */
    public static function float($value): float
    {
        // Se for string, tenta normalizar separadores brasileiros
        if (is_string($value)) {
            $value = str_replace('.', '', $value); // Remove pontos de milhar
            $value = str_replace(',', '.', $value); // Converte vírgula decimal para ponto
        }
        return (float)filter_var($value, FILTER_VALIDATE_FLOAT);
    }
    
    /**
     * Sanitiza CNPJ/CPF (remove não-numéricos)
     */
    public static function documento(string $doc): string
    {
        return preg_replace('/[^0-9]/', '', $doc);
    }
    
    /**
     * Sanitiza telefone
     */
    public static function telefone(string $tel): string
    {
        return preg_replace('/[^0-9+]/', '', $tel);
    }
    
    /**
     * Valida CNPJ
     */
    public static function validarCNPJ(string $cnpj): bool
    {
        $cnpj = self::documento($cnpj);
        
        if (strlen($cnpj) !== 14 || preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }
        
        // Algoritmo de validação
        for ($t = 12; $t < 14; $t++) {
            $d = 0;
            $c = 0;
            for ($m = $t - 7; $m >= 2; $m--, $c++) {
                $d += (int)$cnpj[$c] * $m;
            }
            for ($m = 9; $m >= 2; $m--, $c++) {
                $d += (int)$cnpj[$c] * $m;
            }
            $d = ((10 * $d) % 11) % 10;
            if ((int)$cnpj[$c] !== $d) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Escapa para uso em expressões regulares
     */
    public static function regex(string $text): string
    {
        return preg_quote($text, '/');
    }
    
    /**
     * Sanitiza array recursivamente
     */
    public static function array(array $data, string $type = 'html'): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $safeKey = self::html((string)$key);
            if (is_array($value)) {
                $sanitized[$safeKey] = self::array($value, $type);
            } else {
                $sanitized[$safeKey] = match($type) {
                    'html' => self::html((string)$value),
                    'plain' => self::plain((string)$value),
                    'int' => self::int($value),
                    'float' => self::float($value),
                    default => self::html((string)$value)
                };
            }
        }
        return $sanitized;
    }
    
    /**
     * Limpa input para LIKE do SQL (adiciona %)
     */
    public static function like(string $term): string
    {
        $term = str_replace(['%', '_'], ['\%', '\_'], $term);
        return '%' . $term . '%';
    }
}
