<?php
declare(strict_types=1);

namespace LogAnalyzer\CollectionBuilder;

class IdSequence
{
    private $id = 1;

    public function __construct($initialId = 0)
    {
        $this->id = $initialId;
    }

    public function now(): int
    {
        return $this->id;
    }

    public function update(): self
    {
        $this->id++;

        return $this;
    }
}