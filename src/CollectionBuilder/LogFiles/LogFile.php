<?php
declare(strict_types=1);

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

    public function __construct(string $path, ParserInterface $parser)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException('file not found.');
        }

        parent::__construct($path);
        $this->setFlags($this->getDefaultFlags());
        $this->parser = $parser;
    }

    protected function getDefaultFlags()
    {
        return SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE;
    }

    public function ignoreParsedError($ignore): void
    {
        $this->ignoreParseError = $ignore;
    }

    public function getCurrentParsedLine(): array
    {
        try {
            return $this->parser->parse(parent::current());
        } catch (ReadException $e) {
            if (!$this->ignoreParseError) {
                throw $e;
            }
        }

        return [];
    }

    public function count(): int
    {
        if (is_null($this->count)) {
            $count = exec('wc -l ' . $this->getRealPath());
            $this->count = intval(trim(str_replace($this->getRealPath(), '', $count)));
        }

        return $this->count;
    }

    public function current(): array
    {
        return $this->getCurrentParsedLine();
    }
}
