<?php

namespace LogAnalyzer\CollectionBuilder\LogFiles;

use Clover\Text\LTSV;
use Kassner\LogParser\LogParser;
use LogAnalyzer\CollectionBuilder\Parser\ApacheLogParser;
use LogAnalyzer\CollectionBuilder\Parser\LtsvParser;
use LogAnalyzer\CollectionBuilder\Items\Item;
use LogAnalyzer\CollectionBuilder\Items\ItemInterface;

/**
 * @package LogAnalyzer
 */
class LogFile
{
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
            $this->parser = new ApacheLogParser($this->options['format']);
            // $parser->setFormat('%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"');
        } elseif ($this->type == 'ltsv') {
            $this->parser = new LtsvParser();
        } else {
            throw new \InvalidArgumentException('type is invalid.');
        }
    }

    public function getItem()
    {
        if (is_null($line = $this->getValidLine())) {
            return null;
        }

        $iterable = $this->parser->parse($line);

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
