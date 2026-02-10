<?php

declare(strict_types=1);

namespace ProposalArchitect\Infrastructure;

use ProposalArchitect\Models\BaseProposalModel;

/**
 * SERVIÇO: Agregador de Variáveis
 * Extrai e valida todas as variáveis necessárias para um modelo
 */
class VariableAggregator
{
    /**
     * Extrai todas as variáveis únicas necessárias para o modelo na ordem de aparição
     */
    public function extractRequiredVariables(BaseProposalModel $model): array
    {
        $orderedBlocks = $model->getOrderedBlocks();
        $allVariables = [];
        $seen = [];

        foreach ($orderedBlocks as $block) {
            foreach ($block->variables as $var) {
                if (!isset($seen[$var])) {
                    $seen[$var] = true;
                    $allVariables[] = [
                        'variable' => $var,
                        'block' => $block->identifier,
                        'level' => $block->level->name,
                        'required' => $block->isRequired
                    ];
                }
            }

            // Processar sub-blocos recursivamente
            if ($block->subBlocks) {
                foreach ($block->subBlocks as $subBlock) {
                    foreach ($subBlock->variables as $var) {
                        if (!isset($seen[$var])) {
                            $seen[$var] = true;
                            $allVariables[] = [
                                'variable' => $var,
                                'block' => "{$block->identifier}.{$subBlock->identifier}",
                                'level' => $subBlock->level->name,
                                'required' => $subBlock->isRequired
                            ];
                        }
                    }
                }
            }
        }

        return $allVariables;
    }

    /**
     * Compara variáveis entre dois modelos
     */
    public function diffVariables(BaseProposalModel $modelA, BaseProposalModel $modelB): array
    {
        $varsA = array_column($this->extractRequiredVariables($modelA), 'variable');
        $varsB = array_column($this->extractRequiredVariables($modelB), 'variable');

        return [
            'only_in_a' => array_diff($varsA, $varsB),
            'only_in_b' => array_diff($varsB, $varsA),
            'common' => array_intersect($varsA, $varsB),
            'total_unique_a' => count(array_unique($varsA)),
            'total_unique_b' => count(array_unique($varsB))
        ];
    }
}
