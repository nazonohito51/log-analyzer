<?php
declare(strict_types=1);

namespace LogAnalyzer\View;

use LogAnalyzer\Collection;

abstract class AbstractColumnStrategy implements ColumnStrategyInterface
{
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function name()
    {
        return $this->name;
    }

    abstract public function __invoke(Collection $collection);
}
