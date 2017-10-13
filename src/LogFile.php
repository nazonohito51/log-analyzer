<?php

namespace LogAnalyzer;

use Clover\Text\LTSV;
use Kassner\LogParser\LogParser;
use LogAnalyzer\Items\Item;
use LogAnalyzer\Items\ItemInterface;

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
    private $type;
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

        $this->type = $this->file->getExtension() == 'ltsv' ? 'ltsv' : 'apache';
        if (isset($options['type'])) {
            $this->type = $options['type'];
        }
        $this->options = [
            'item' => isset($options['item']) ? $options['item'] : Item::class,
            'format' => isset($options['format']) ? $options['format'] : null,
        ];

        if ($this->type == 'apache') {
            $this->parser = new LogParser($this->options['format']);
            // $parser->setFormat('%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"');
        } elseif ($this->type == 'ltsv') {
            $this->parser = new LTSV();
        } else {
            throw new \InvalidArgumentException('type is invalid.');
        }
    }

    public function getItem()
    {
        if (is_null($line = $this->getValidLine())) {
            return null;
        }

        if ($this->type == 'apache') {
            $iterable = $this->parser->parse($line);
        } elseif ($this->type == 'ltsv') {
            $iterable = $this->parser->parseLine($line);
        } else {
            throw new \LogicException('type is invalid.');
        }

        return new $this->options['item']($iterable);
    }

    /**
     * @return ItemInterface[]
     */
    public function getItems()
    {
        $items = [];

        while (!is_null($item = $this->getItem())) {
            $items[] = $item;
        }

        return $items;
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
