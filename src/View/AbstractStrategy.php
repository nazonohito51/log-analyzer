<?php
declare(strict_types=1);

namespace LogAnalyzer\View;

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
