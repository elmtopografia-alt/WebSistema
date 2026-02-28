<?php
/**
 * SGT TemaEngine v2
 * Gerencia cores e estilos do sistema
 * Compatível: PHP 7.0+
 */

class TemaEngine 
{
    private static $CORES = [
        'azul' => [
            'primaria'   => '1e3a8a',
            'secundaria' => '3b82f6',
            'fundo'      => 'eff6ff',
            'texto'      => '1e40af',
            'nome'       => 'Corporativo'
        ],
        'verde' => [
            'primaria'   => '065f46',
            'secundaria' => '10b981',
            'fundo'      => 'ecfdf5',
            'texto'      => '047857',
            'nome'       => 'Topografia'
        ],
        'laranja' => [
            'primaria'   => '7c2d12',
            'secundaria' => 'f59e0b',
            'fundo'      => 'fffbeb',
            'texto'      => '92400e',
            'nome'       => 'Energia'
        ],
        'cinza' => [
            'primaria'   => '1f2937',
            'secundaria' => '6b7280',
            'fundo'      => 'f9fafb',
            'texto'      => '374151',
            'nome'       => 'Institucional'
        ]
    ];

    private $corAtiva;

    public function __construct($cor = 'verde')
    {
        $this->corAtiva = isset(self::$CORES[$cor]) ? $cor : 'verde';
    }

    public function getCssUrl()
    {
        return "temas/tema.php?cor={$this->corAtiva}";
    }

    public function getPaleta()
    {
        return self::$CORES[$this->corAtiva];
    }

    public function getCorAtiva()
    {
        return $this->corAtiva;
    }

    public static function listarCores()
    {
        $nomes = [];
        foreach (self::$CORES as $key => $c) {
            $nomes[$key] = $c['nome'];
        }
        return $nomes;
    }

    public static function coresDisponiveis()
    {
        return array_keys(self::$CORES);
    }
}
