<?php
namespace LogAnalyzer\CollectionBuilder\Parser;

use Kassner\LogParser\FormatException;
use Kassner\LogParser\LogParser;
use LogAnalyzer\Exception\ReadException;

class ApacheLogParser implements ParserInterface
{
    protected static $defaultFormat = '%h %l %u %t "%r" %>s %b';

    protected $format;

    /**
     * @param string|null $format parse format of kassner/log-parser(https://github.com/kassner/log-parser)
     */
    public function __construct($format = null)
    {
//        $this->format = is_null($format) ? self::$defaultFormat : $format;
        $this->format = $format;
    }

    public function parse($line)
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
