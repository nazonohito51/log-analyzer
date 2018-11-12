<?php
declare(strict_types=1);

namespace LogAnalyzer\CollectionBuilder\Parser;

use Clover\Text\LTSV;

class LtsvParser implements ParserInterface
{
    public function parse($line): array
    {
        $parser = new Ltsv();
        return $parser->parseLine($line);
    }
}
