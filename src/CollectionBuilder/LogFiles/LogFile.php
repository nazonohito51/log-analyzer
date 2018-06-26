<?php
namespace LogAnalyzer\CollectionBuilder\LogFiles;

use LogAnalyzer\CollectionBuilder\Items\Item;
use LogAnalyzer\CollectionBuilder\Items\ItemInterface;
use LogAnalyzer\CollectionBuilder\Parser\ParserInterface;
use LogAnalyzer\Exception\InvalidArgumentException;

class LogFile
{
    private $parser;
    private $file;
    private $itemClass;

    /**
     * @param $path
     * @param ParserInterface $parser
     * @param string $itemClass
     */
    public function __construct($path, ParserInterface $parser, $itemClass = null)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException('file not found.');
        } elseif  (!is_null($itemClass) && !class_exists($itemClass)) {
            throw new InvalidArgumentException('item class is not found.');
        }
        $this->file = new \SplFileObject($path);
        $this->parser = $parser;
        $this->itemClass = !is_null($itemClass) ? $itemClass : Item::class;
    }

    public function getItem()
    {
        if (is_null($line = $this->getValidLine())) {
            return null;
        }

        $iterable = $this->parser->parse($line);

        return new $this->itemClass($iterable);
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
