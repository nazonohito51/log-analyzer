<?php
declare(strict_types=1);

namespace LogAnalyzer\CollectionBuilder\Parser;

use Kassner\LogParser\FormatException;
use Kassner\LogParser\LogParser;
use LogAnalyzer\Exception\ReadException;

class ApacheLogParser implements ParserInterface
{
    protected $format;

    /**
     * @param string|null $format parse format of kassner/log-parser(https://github.com/kassner/log-parser)
     */
    public function __construct($format = null)
    {
        $this->format = $format;
    }

    public function parse($line): array
    {
        try {
            $parser = new LogParser($this->format);
            $ret = $parser->parse($line);

            return (array)$ret;
        } catch (FormatException $e) {
            throw new ReadException($line);
        }
    }
}
