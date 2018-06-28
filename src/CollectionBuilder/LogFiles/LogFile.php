<?php
namespace LogAnalyzer\CollectionBuilder\LogFiles;

use LogAnalyzer\CollectionBuilder\Parser\ParserInterface;
use LogAnalyzer\Exception\InvalidArgumentException;
use SplFileObject;

class LogFile extends \SplFileObject
{
    private $parser;

    /**
     * @param $path
     * @param ParserInterface $parser
     */
    public function __construct($path, ParserInterface $parser)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException('file not found.');
        }

        parent::__construct($path);
        $this->setFlags(SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $this->parser = $parser;
    }

    public function current()
    {
        return $this->parser->parse(parent::current());
    }

    public function getLineCount()
    {
        $count = exec('wc -l ' . $this->getRealPath());
        return trim(str_replace($this->getRealPath(), '', $count));
    }
}
