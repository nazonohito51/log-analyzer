<?php
namespace LogAnalyzer\CollectionBuilder\LogFiles;

use LogAnalyzer\CollectionBuilder\Items\Item;
use LogAnalyzer\CollectionBuilder\Items\ItemInterface;
use LogAnalyzer\CollectionBuilder\Parser\ParserInterface;

/**
 * @package LogAnalyzer
 */
class LogFile
{
    private $parser;

    private $file;
    private $options;

    /**
     * acceptable file: [apache log / ltsv]
     * @param $path
     * @param ParserInterface $parser
     * @param array $options
     */
    public function __construct($path, ParserInterface $parser, array $options = [])
    {
        $this->file = new \SplFileObject($path);
        if (!$this->file->isFile()) {
            throw new \InvalidArgumentException();
        }

        $this->parser = $parser;
        $this->options = [
            'item' => isset($options['item']) ? $options['item'] : Item::class,
        ];
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
