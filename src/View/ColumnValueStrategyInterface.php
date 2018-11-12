<?php
declare(strict_types=1);

namespace LogAnalyzer\View;

use LogAnalyzer\Collection;

interface ColumnValueStrategyInterface
{
    public function columnHeader();
    public function __invoke(Collection $collection);
}
