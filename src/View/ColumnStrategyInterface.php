<?php
declare(strict_types=1);

namespace LogAnalyzer\View;

use LogAnalyzer\Collection;

interface ColumnStrategyInterface
{
    public function name();
    public function __invoke(Collection $collection);
}
