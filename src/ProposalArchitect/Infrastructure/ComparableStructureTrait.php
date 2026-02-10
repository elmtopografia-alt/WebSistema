<?php

declare(strict_types=1);

namespace ProposalArchitect\Infrastructure;

use ProposalArchitect\Models\BaseProposalModel;

/**
 * TRAIT: Capacidade de Comparação
 * Para uso em modelos que precisam comparar-se com outros
 */
trait ComparableStructureTrait
{
    public function compareWith(BaseProposalModel $other): array
    {
        $mySequence = array_map(
            fn($b) => $b->identifier,
            $this->getOrderedBlocks()
        );
        $otherSequence = array_map(
            fn($b) => $b->identifier,
            $other->getOrderedBlocks()
        );

        return [
            'similarity' => $this->calculateSimilarity($mySequence, $otherSequence),
            'common_blocks' => array_intersect($mySequence, $otherSequence),
            'sequence_diff' => $this->diffSequences($mySequence, $otherSequence),
            'models' => [
                'a' => $this->getModelMetadata()['codename'],
                'b' => $other->getModelMetadata()['codename']
            ]
        ];
    }

    private function calculateSimilarity(array $a, array $b): float
    {
        $intersection = count(array_intersect($a, $b));
        $union = count(array_unique(array_merge($a, $b)));
        return $union > 0 ? $intersection / $union : 0.0;
    }

    private function diffSequences(array $a, array $b): array
    {
        $diff = [];
        $max = max(count($a), count($b));

        for ($i = 0; $i < $max; $i++) {
            $itemA = $a[$i] ?? null;
            $itemB = $b[$i] ?? null;

            if ($itemA !== $itemB) {
                $diff[] = [
                    'position' => $i,
                    'this_model' => $itemA,
                    'other_model' => $itemB
                ];
            }
        }

        return $diff;
    }
}
