<?php
namespace LogAnalyzer\View\ColumnValueStrategy;

use LogAnalyzer\Collection;

class CountStrategy extends AbstractStrategy
{
    public function __invoke(Collection $collection)
    {
        return $collection->count();
    }
}
