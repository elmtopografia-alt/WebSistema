<?php

namespace ProposalArchitect\Infrastructure;

use ProposalArchitect\Models\BaseProposalModel;

/**
 * SERVIÇO: Construtor de Árvore Hierárquica
 * Transforma sequência linear em estrutura de árvore
 * (Versão adaptada para PHP 5.6)
 */
class HierarchyTreeBuilder
{
    /**
     * @param BaseProposalModel $model
     * @return array
     */
    public function build(BaseProposalModel $model)
    {
        $sequence = $model->getOrderedBlocks();
        $tree = [];
        // Stack mantém referências para onde estamos na árvore
        $stack = [&$tree];

        // Mapeamento de níveis para profundidade numérica (0=root, 1=section, etc)
        // Como agora Level é string, precisamos desse mapa manual
        $depthMap = [
            'root' => 0,
            'section' => 1,
            'sub_section' => 2,
            'detail' => 3
        ];

        foreach ($sequence as $block) {

            // Garantir que temos um nível válido
            $levelName = $block->level;
            $depth = isset($depthMap[$levelName]) ? $depthMap[$levelName] : 0;

            $node = [
                'id' => $block->id,
                'title' => $block->name,
                'level' => $levelName,
                'category' => $block->category,
                'required' => $block->isRequired,
                'default_content' => $block->defaultContent,
                'children' => []
            ];

            // Ajustar pilha baseada na profundidade
            // (Simulação simples: se o bloco atual é nível 1, ele deve ser filho do nível 0)

            // Se a profundidade atual da pilha for maior que a do bloco, volta níveis
            while (count($stack) > ($depth + 1)) {
                array_pop($stack);
            }

            // O pai é o último elemento da pilha
            $parent = &$stack[count($stack) - 1];

            // Adiciona o nó atual aos filhos do pai
            $parent[] = $node;

            // Pega a referência do nó recém adicionado para ser possível pai futuro
            // O último elemento adicionado ao pai é $parent[count($parent)-1]
            // Mas em PHP arrays associativos, node se torna um item.
            // Precisamos apontar para o campo 'children' dele.

            // IMPORTANTE: Em PHP 5.6 com arrays, referências podem ser complexas.
            // Vamos simplificar: O nó que acabamos de adicionar está no final do array $parent.
            end($parent);
            $key = key($parent);

            // Adiciona referência aos filhos desse novo nó na pilha
            $stack[] = &$parent[$key]['children'];
        }

        return $tree;
    }
}
