<?php
namespace LogAnalyzer\View\ColumnValueStrategy;

use LogAnalyzer\Collection;

class UniqueValuesStrategy extends AbstractStrategy
{
    protected $columnName;

    public function __construct($columnName, $dimensionColumnName)
    {
        parent::__construct($dimensionColumnName);
        $this->columnName = $columnName;
    }

    public function __invoke(Collection $collection)
    {
        return array_unique($collection->columnValues($this->columnName));
    }
}