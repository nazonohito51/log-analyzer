<?php
namespace LogAnalyzer\View\ColumnValueStrategy;

use LogAnalyzer\Collection;

abstract class AbstractStrategy
{
    protected $dimensionColumnName;

    public function __construct($dimensionColumnName)
    {
        $this->dimensionColumnName = $dimensionColumnName;
    }

    abstract public function __invoke(Collection $collection);
}
