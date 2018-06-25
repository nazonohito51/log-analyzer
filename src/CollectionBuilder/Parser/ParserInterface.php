<?php
namespace LogAnalyzer\CollectionBuilder\Parser;

interface ParserInterface
{
    /**
     * Parse line
     * @param string $line
     * @return array
     */
    public function parse($line);
}
