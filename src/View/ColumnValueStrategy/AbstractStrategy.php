<?php
namespace LogAnalyzer\View\ColumnValueStrategy;

use LogAnalyzer\Collection;

abstract class AbstractStrategy
{
    abstract public function __invoke(Collection $collection, $dimensionValue);
}
