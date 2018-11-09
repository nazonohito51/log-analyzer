<?php
declare(strict_types=1);

namespace LogAnalyzer\CollectionBuilder\LogFiles;

use LogAnalyzer\CollectionBuilder\Parser\ParserInterface;
use LogAnalyzer\Exception\InvalidArgumentException;
use LogAnalyzer\Exception\ReadException;
use SplFileObject;

class LogFile extends \SplFileObject
{
    /**
     * @var ParserInterface $parser
     */
    private $parser;

    /**
     * @var int $count
     */
    private $count;

    /**
     * @var bool $ignoreParseError
     */
    private $ignoreParseError = false;

    public function __construct(string $path, ParserInterface $parser)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException('file not found.');
        }

        parent::__construct($path);
        $this->setFlags(SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $this->parser = $parser;

        $count = exec('wc -l ' . $this->getRealPath());
        $this->count = intval(trim(str_replace($this->getRealPath(), '', $count)));
    }

    public function ignoreParsedError($ignore): void
    {
        $this->ignoreParseError = $ignore;
    }

    public function getCurrentParsedLine(): ?array
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

    public function current(): ?array
    {
        return $this->getCurrentParsedLine();
    }

    public function count(): int
    {
        return $this->count;
    }
}
