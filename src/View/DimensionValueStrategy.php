<?php
declare(strict_types=1);

namespace LogAnalyzer\View;

use LogAnalyzer\Collection;
use LogAnalyzer\View\AbstractStrategy;

class DimensionValueStrategy extends AbstractStrategy
{
    public function __invoke(Collection $collection)
    {
        return $collection->values($this->dimensionColumnName);
    }
}
