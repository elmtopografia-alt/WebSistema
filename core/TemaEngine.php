<?php
/**
 * SGT TemaEngine v2
 * Gerencia cores e estilos do sistema
 */

class TemaEngine 
{
    private const CORES = [
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

    private string $corAtiva;

    public function __construct(string $cor = 'verde')
    {
        $this->corAtiva = isset(self::CORES[$cor]) ? $cor : 'verde';
    }

    public function getCssUrl(): string
    {
        return "temas/tema.php?cor={$this->corAtiva}";
    }

    public function getPaleta(): array
    {
        return self::CORES[$this->corAtiva];
    }

    public function getCorAtiva(): string
    {
        return $this->corAtiva;
    }

    public static function listarCores(): array
    {
        return array_map(fn($c) => $c['nome'], self::CORES);
    }

    public static function coresDisponiveis(): array
    {
        return array_keys(self::CORES);
    }
}
