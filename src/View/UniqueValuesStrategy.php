<?php
declare(strict_types=1);

namespace LogAnalyzer\View;

use LogAnalyzer\Collection;
use LogAnalyzer\View\AbstractColumnValueStrategy;

class UniqueValuesStrategy extends AbstractColumnValueStrategy
{
    protected $columnName;

    public function __construct(string $columnHeader)
    {
        parent::__construct($columnHeader);
        $this->columnName = $columnHeader;
    }

    public function __invoke(Collection $collection)
    {
        return array_unique($collection->values($this->columnName));
    }
}
