<?php
declare(strict_types=1);

namespace LogAnalyzer\View;

use LogAnalyzer\Collection;
use LogAnalyzer\View\AbstractColumnValueStrategy;

class CountStrategy extends AbstractColumnValueStrategy
{
    const HEADER = '_count';

    public function __construct()
    {
        parent::__construct(self::HEADER);
    }

    public function __invoke(Collection $collection)
    {
        return $collection->count();
    }
}
