<?php
namespace LogAnalyzer\CollectionBuilder\Parser;

use LogAnalyzer\Exception\ReadException;

interface ParserInterface
{
    /**
     * Parse line
     * @param string $line
     * @return array
     * @throws ReadException
     */
    public function parse($line);
}
