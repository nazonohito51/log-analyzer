<?php
declare(strict_types=1);

namespace LogAnalyzer\View;

use LogAnalyzer\Collection;

class ColumnValueStrategyFactory
{
    public function build(string $columnHeader, callable $procedure = null): ColumnValueStrategyInterface
    {
        return is_null($procedure) ? new UniqueValuesStrategy($columnHeader) : $this->buildByClosure($columnHeader, $procedure);
    }

    protected function buildByClosure(string $columnHeader, callable $procedure): ColumnValueStrategyInterface
    {
        return new class ($procedure, $columnHeader) extends AbstractColumnValueStrategy
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
