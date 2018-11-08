<?php
namespace LogAnalyzer\CollectionBuilder;

use LogAnalyzer\CollectionBuilder\Items\ItemInterface;
use LogAnalyzer\Database\Column\ColumnFactory;
use LogAnalyzer\Database\DatabaseInterface;
use LogAnalyzer\Database\ColumnarDatabase;
use LogAnalyzer\View;

class Collection implements \Countable, \IteratorAggregate
{
    protected $itemIds;
    protected $database;

    /**
     * @param int[] $items
     * @param DatabaseInterface $database
     */
    public function __construct(array $items, DatabaseInterface $database = null)
    {
        $this->itemIds = $items;
        // TODO: fix $database in argument
        $this->database = !is_null($database) ? $database : new ColumnarDatabase(new ColumnFactory());
    }

    public function count()
    {
        return count($this->itemIds);
    }

    public function dimension($columnName, callable $procedure = null)
    {
//        $progressBar = new View\ProgressBar($this->count());

        $itemIdsByValue = [];
        foreach ($this->database->getColumnSubset($columnName, $this->itemIds) as $value => $itemIds) {
            $calcValue = $this->calcValue($value, $procedure);
            $itemIdsByValue[$calcValue] = array_merge($itemIds, $itemIdsByValue[$calcValue] ?? []);
        }

        $collections = [];
        foreach ($itemIdsByValue as $value => $itemIds) {
            $collections[$value] = new self($itemIds, $this->database);
        }

        return new View($columnName, $collections);
    }

    public function map($columnName, callable $procedure = null)
    {
        $ret = [];
        foreach ($this->itemIds as $itemId) {
            $value = $this->database->getValue($columnName, $itemId);
            $ret[] = $this->calcValue($value, $procedure);
        }

        return $ret;
    }

    /**
     * Build new collection on items satisfied with callable
     * @param callable $procedure
     * @return Collection
     */
    public function filter(callable $procedure)
    {
        $items = [];
        foreach ($this->itemIds as $item) {
            if ($procedure($item) === true) {
                $items[] = $item;
            }
        }

        return new self($items);
    }

    protected function calcValue($value, callable $procedure = null)
    {
        if (!is_null($procedure)) {
            $value = $procedure($value) ?? 'null';
        }

        return $value;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->itemIds);
    }
}
