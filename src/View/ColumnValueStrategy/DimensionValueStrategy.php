<?php
namespace LogAnalyzer\View\ColumnValueStrategy;

use LogAnalyzer\Collection;

class DimensionValueStrategy extends AbstractStrategy
{
    public function __invoke(Collection $collection, $dimensionValue)
    {
        return $dimensionValue;
    }
}
