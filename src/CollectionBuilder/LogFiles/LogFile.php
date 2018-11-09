<?php
namespace LogAnalyzer\CollectionBuilder\LogFiles;

use LogAnalyzer\CollectionBuilder\Parser\ParserInterface;
use LogAnalyzer\Exception\InvalidArgumentException;
use LogAnalyzer\Exception\ReadException;
use SplFileObject;

class LogFile extends \SplFileObject
{
    private $parser;
    private $count;
    private $ignoreParseError = false;

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

        $count = exec('wc -l ' . $this->getRealPath());
        $this->count = trim(str_replace($this->getRealPath(), '', $count));
    }

    public function ignoreParsedError($ignore)
    {
        $this->ignoreParseError = $ignore;
    }

    public function getCurrentParsedLine()
    {
        try {
            return $this->parser->parse(parent::current());
        } catch (ReadException $e) {
            if (!$this->ignoreParseError) {
                throw $e;
            }
        }

        return null;
    }

    public function current()
    {
        return $this->getCurrentParsedLine();
    }

    public function count()
    {
        return $this->count;
    }
}
