<?php

namespace ProposalArchitect\Models;

/**
 * Define níveis hierárquicos dos blocos.
 * (Refatorado de Enum para Class Const para compatibilidade PHP 5.6)
 */
class BlockLevel
{
    const ROOT = 'root';
    const SECTION = 'section';
    const SUB_SECTION = 'sub_section';
    const DETAIL = 'detail';

    /**
     * Retorna se é um nível container (pode ter filhos)
     * @param string $level
     * @return bool
     */
    public static function isContainer($level)
    {
        return in_array($level, [self::ROOT, self::SECTION, self::SUB_SECTION]);
    }
}
