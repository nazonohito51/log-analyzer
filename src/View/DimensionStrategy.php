<?php
declare(strict_types=1);

namespace LogAnalyzer\View;

use LogAnalyzer\Collection;

class DimensionStrategy extends AbstractColumnValueStrategy
{
    protected $columnName;

    public function __construct(string $columnHeader)
    {
        parent::__construct($columnHeader);
        $this->columnName = $columnHeader;
    }

    public function __invoke(Collection $collection)
    {
        return $collection->values($this->columnName);
    }
}
