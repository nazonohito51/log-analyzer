<?php
declare(strict_types=1);

namespace LogAnalyzer\View;

use LogAnalyzer\Collection;

class ColumnStrategyFactory
{
    public function build(string $columnHeader, callable $procedure = null): ColumnStrategyInterface
    {
        return is_null($procedure) ? new UniqueValuesStrategy($columnHeader) : $this->buildByClosure($columnHeader, $procedure);
    }

    protected function buildByClosure(string $columnHeader, callable $procedure): ColumnStrategyInterface
    {
        return new class ($procedure, $columnHeader) extends AbstractColumnStrategy
        {
            private $procedure;

            public function __construct(callable $procedure, string $name)
            {
                parent::__construct($name);
                $this->procedure = $procedure;
            }

            public function __invoke(Collection $collection)
            {
                return $this->procedure->__invoke($collection);
            }
        };
    }
}
