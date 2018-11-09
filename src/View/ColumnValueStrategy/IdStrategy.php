<?php
namespace LogAnalyzer\View\ColumnValueStrategy;

use LogAnalyzer\Collection;
use LogAnalyzer\CollectionBuilder\IdSequence;

class IdStrategy extends AbstractStrategy
{
    private $sequence;

    public function __construct(IdSequence $sequence)
    {
        $this->sequence = $sequence;
    }

    public function __invoke(Collection $collection, $dimensionValue)
    {
        return $this->sequence->update()->now();
    }
}
