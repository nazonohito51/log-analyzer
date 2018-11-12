<?php
declare(strict_types=1);

namespace LogAnalyzer\View;

use LogAnalyzer\Collection;
use LogAnalyzer\CollectionBuilder\IdSequence;
use LogAnalyzer\View\AbstractColumnValueStrategy;

class IdStrategy extends AbstractColumnValueStrategy
{
    private $sequence;

    public function __construct(IdSequence $sequence, string $columnHeader)
    {
        parent::__construct($columnHeader);
        $this->sequence = $sequence;
    }

    public function __invoke(Collection $collection)
    {
        return $this->sequence->update()->now();
    }
}
