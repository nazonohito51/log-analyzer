<?php
declare(strict_types=1);

namespace LogAnalyzer\View;

use LogAnalyzer\Collection;
use LogAnalyzer\View\AbstractColumnStrategy;

class UniqueValuesStrategy extends AbstractColumnStrategy
{
    protected $columnName;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->columnName = $name;
    }

    public function __invoke(Collection $collection)
    {
        return array_unique($collection->values($this->columnName));
    }
}
