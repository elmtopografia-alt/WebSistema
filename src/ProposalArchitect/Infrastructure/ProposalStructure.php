<?php

declare(strict_types=1);

namespace ProposalArchitect\Infrastructure;

use ProposalArchitect\Models\BlockDefinition;

/**
 * OBJETO DE VALOR: Estrutura Compilada
 * Representação imutável da estrutura final processada
 */
final class ProposalStructure
{
    public function __construct(
        public readonly string $modelCode,
        public readonly array $sequence,           // Array de BlockDefinition ordenado
        public readonly array $variableMap,        // Map identifier => variables[]
        public readonly int $complexityScore,      // Métrica de complexidade estrutural
        public readonly array $hierarchyTree       // Representação em árvore
    ) {}

    /**
     * Serialização para cache ou transmissão
     */
    public function toArray(): array
    {
        return [
            'model' => $this->modelCode,
            'block_count' => count($this->sequence),
            'total_variables' => count(array_unique(array_merge(...array_values($this->variableMap)))),
            'complexity' => $this->complexityScore,
            'hierarchy' => $this->hierarchyTree
        ];
    }
}
