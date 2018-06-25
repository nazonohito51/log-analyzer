<?php
namespace LogAnalyzer\CollectionBuilder\Parser;

use Clover\Text\LTSV;

class LtsvParser implements ParserInterface
{
    public function parse($line)
    {
        $parser = new Ltsv();
        return $parser->parseLine($line);
    }
}
