<?php
declare(strict_types=1);

namespace LogAnalyzer\View;

use LogAnalyzer\Collection;
use LogAnalyzer\View\AbstractStrategy;

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
        return array_unique($collection->values($this->columnName));
    }
}
