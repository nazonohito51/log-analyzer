<?php

namespace LogAnalyzer;

use Clover\Text\LTSV;
use Kassner\LogParser\LogParser;
use LogAnalyzer\Entries\Entry;

/**
 * @package LogAnalyzer
 */
class LogFile
{
    /**
     * @var LogParser|LTSV
     */
    private $parser;

    private $file;
    private $log_type;
    private $options;

    /**
     * acceptable file: [apache log / ltsv]
     * @param $path
     * @param array $options
     */
    public function __construct($path, array $options = [])
    {
        $this->file = new \SplFileObject($path);
        if (!$this->file->isFile()) {
            throw new \InvalidArgumentException();
        }

        $this->log_type = $this->file->getExtension() == 'ltsv' ? 'ltsv' : 'apache';
        if (isset($options['log_type'])) {
            $this->log_type = $options['log_type'];
        }
        $this->options = [
            'format' => isset($options['format']) ? $options['format'] : null
        ];

        if ($this->log_type == 'apache') {
            $this->parser = new LogParser($this->options['format']);
            // $parser->setFormat('%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"');
        } elseif ($this->log_type == 'ltsv') {
            $this->parser = new LTSV();
        } else {
            throw new \InvalidArgumentException('log_type is invalid.');
        }
    }

    public function getEntry()
    {
        if (is_null($line = $this->getValidLine())) {
            return null;
        }

        if ($this->log_type == 'apache') {
            $iterable = $this->parser->parse($line);
        } elseif ($this->log_type == 'ltsv') {
            $iterable = $this->parser->parseLine($line);
        } else {
            throw new \LogicException('log_type is invalid.');
        }

        return new Entry($iterable);
    }

    public function getEntries()
    {
        $entries = [];

        while (!is_null($entry = $this->getEntry())) {
            $entries[] = $entry;
        }

        return $entries;
    }

    /**
     * get line. ignore empty line.
     * @return null|string
     */
    private function getValidLine()
    {
        while (!$this->file->eof()) {
            $line = $this->file->getCurrentLine();
            if (!empty(trim($line))) {
                return $line;
            }
        }

        return null;
    }
}
