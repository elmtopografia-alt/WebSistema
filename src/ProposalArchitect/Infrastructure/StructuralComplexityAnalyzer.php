<?php

declare(strict_types=1);

namespace ProposalArchitect\Infrastructure;

use ProposalArchitect\Models\BaseProposalModel;

/**
 * SERVIÇO: Analisador de Complexidade Estrutural
 * Calcula métricas de peso/complexidade do documento
 */
class StructuralComplexityAnalyzer
{
    public function analyze(BaseProposalModel $model): array
    {
        $blocks = $model->getOrderedBlocks();
        $metrics = [
            'total_blocks' => 0,
            'total_sub_blocks' => 0,
            'depth_max' => 0,
            'required_ratio' => 0,
            'category_distribution' => [],
            'level_distribution' => []
        ];

        $requiredCount = 0;

        foreach ($blocks as $block) {
            $metrics['total_blocks']++;
            $metrics['category_distribution'][$block->category->value] =
                ($metrics['category_distribution'][$block->category->value] ?? 0) + 1;
            $metrics['level_distribution'][$block->level->name] =
                ($metrics['level_distribution'][$block->level->name] ?? 0) + 1;

            if ($block->isRequired) $requiredCount++;
            if ($block->level->value > $metrics['depth_max']) {
                $metrics['depth_max'] = $block->level->value;
            }

            if ($block->subBlocks) {
                foreach ($block->subBlocks as $sub) {
                    $metrics['total_sub_blocks']++;
                    if ($sub->level->value > $metrics['depth_max']) {
                        $metrics['depth_max'] = $sub->level->value;
                    }
                }
            }
        }

        $total = $metrics['total_blocks'] + $metrics['total_sub_blocks'];
        $metrics['required_ratio'] = $total > 0 ? $requiredCount / $metrics['total_blocks'] : 0;
        $metrics['complexity_score'] = $this->calculateScore($metrics);

        return $metrics;
    }

    private function calculateScore(array $metrics): int
    {
        // Algoritmo de pontuação: blocos * profundidade * fator de granularidade
        $base = $metrics['total_blocks'] * 10;
        $depthMultiplier = 1 + ($metrics['depth_max'] * 0.2);
        $detailPenalty = $metrics['total_sub_blocks'] * 2;

        return (int) (($base * $depthMultiplier) + $detailPenalty);
    }
}
