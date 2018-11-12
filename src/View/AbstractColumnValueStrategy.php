<?php
declare(strict_types=1);

namespace LogAnalyzer\View;

use LogAnalyzer\Collection;

abstract class AbstractColumnValueStrategy
{
    protected $columnHeader;

    public function __construct(string $columnHeader)
    {
        $this->columnHeader = $columnHeader;
    }

    public function columnHeader()
    {
        return $this->columnHeader;
    }

    abstract public function __invoke(Collection $collection);
}
