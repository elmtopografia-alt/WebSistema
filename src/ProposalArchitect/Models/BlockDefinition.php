<?php

namespace ProposalArchitect\Models;

/**
 * Representa a definição de um bloco na estrutura.
 * Adaptado para PHP 5.6+
 */
class BlockDefinition
{
    /** @var string */
    public $id;

    /** @var string */
    public $name;

    /** @var BlockLevel */
    public $level;

    /** @var BlockCategory */
    public $category;

    /** @var bool */
    public $isRequired;

    /** @var array */
    public $requiredVars;

    /** @var string */
    public $defaultContent;

    /** @var array */
    public $children;

    /**
     * @param string $id
     * @param string $name
     * @param BlockLevel $level
     * @param BlockCategory $category
     * @param bool $isRequired
     * @param array $requiredVars
     * @param string $defaultContent
     * @param array $children
     */
    public function __construct(
        $id,
        $name,
        $level,
        $category = null,
        $isRequired = true,
        $requiredVars = [],
        $defaultContent = '',
        $children = []
    ) {
        // Fallback para valor default de enum/objeto caso venha nulo
        if ($category === null) {
            $category = BlockCategory::COMMERCIAL;
        }

        $this->id = $id;
        $this->name = $name;
        $this->level = $level;
        $this->category = $category;
        $this->isRequired = $isRequired;
        $this->requiredVars = $requiredVars;
        $this->defaultContent = $defaultContent;
        $this->children = $children;
    }
}
