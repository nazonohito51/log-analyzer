<?php
namespace LogAnalyzer\View\ColumnValueStrategy;

use LogAnalyzer\Collection;

class UniqueValuesStrategy extends AbstractStrategy
{
    private $columnName;

    public function __construct($columnName)
    {
        $this->columnName = $columnName;
    }

    public function __invoke(Collection $collection, $dimensionValue)
    {
        return array_unique($collection->columnValues($this->columnName));
    }
}