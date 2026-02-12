<?php

declare(strict_types=1);

namespace ProposalArchitect\Infrastructure;

/**
 * REGISTRO GLOBAL E CACHE DE ESTRUTURAS
 * Singleton para manter estruturas em memória
 */
class StructureRegistry
{
    private static $instance = null;
    private $cache = [];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register(string $key, ProposalStructure $structure): void
    {
        $this->cache[$key] = $structure;
    }

    public function get(string $key): ?ProposalStructure
    {
        return $this->cache[$key] ?? null;
    }

    public function clear(): void
    {
        $this->cache = [];
    }
}
