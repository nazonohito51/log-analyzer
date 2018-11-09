<?php
namespace LogAnalyzer;

use LogAnalyzer\CollectionBuilder\Items\ItemInterface;
use LogAnalyzer\Database\Column\ColumnFactory;
use LogAnalyzer\Database\DatabaseInterface;
use LogAnalyzer\Database\ColumnarDatabase;
use LogAnalyzer\View;

class Collection implements \Countable, \IteratorAggregate
{
    protected $itemIds;
    protected $database;
    protected $cache = [];

    /**
     * @param int[] $items
     * @param DatabaseInterface $database
     */
    public function __construct(array $items, DatabaseInterface $database)
    {
        $this->itemIds = $items;
        $this->database = $database;
    }

    public function count()
    {
        return count($this->itemIds);
    }

    public function dimension($columnName, callable $procedure = null)
    {
        $itemIdsByValue = [];
        foreach ($this->database->getColumnSubset($columnName, $this->itemIds) as $value => $itemIds) {
            $calcValue = $this->calcValue($value, $procedure);
            $itemIdsByValue[$calcValue] = array_merge($itemIds, $itemIdsByValue[$calcValue] ?? []);
        }

        $collections = [];
        foreach ($itemIdsByValue as $value => $itemIds) {
            $collections[$value] = new self($itemIds, $this->database);
            // For performance, cache dimension value.
            $collections[$value]->cacheColumnValues($columnName, $value);
        }

        return new View($columnName, $collections);
    }

    public function columnValues($columnName)
    {
        if (isset($this->cache[$columnName])) {
            return $this->cache[$columnName];
        }

        $ret = [];
        foreach ($this->itemIds as $itemId) {
            if (!is_null($value = $this->database->getValue($columnName, $itemId))) {
                $ret[] = $value;
            }
        }

        return $ret;
    }

    public function cacheColumnValues($columnName, $cacheValue)
    {
        $this->cache[$columnName] = $cacheValue;
    }

    public function flushCache()
    {
        $this->cache = [];
    }

    public function filter($columnName, callable $procedure)
    {
        $itemIds = [];
        foreach ($this->itemIds as $itemId) {
            if ($procedure($this->database->getValue($columnName, $itemId)) === true) {
                $itemIds[] = $itemId;
            }
        }

        return new self($itemIds, $this->database);
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
