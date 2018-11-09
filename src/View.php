<?php
namespace LogAnalyzer;

use LogAnalyzer\Collection;
use LogAnalyzer\View\ColumnValueStrategy\AbstractStrategy;
use LogAnalyzer\View\ColumnValueStrategy\CountStrategy;
use LogAnalyzer\View\ColumnValueStrategy\DimensionValueStrategy;
use LogAnalyzer\View\ColumnValueStrategy\UniqueValuesStrategy;
use LogAnalyzer\View\TableView;
use LucidFrame\Console\ConsoleTable;

class View implements \Countable
{
    const COUNT_COLUMN = '_count';

    protected $dimension;
    protected $columns;
    protected $collections;

    /**
     * @param string $dimension
     * @param Collection[] $collections
     */
    public function __construct($dimension, array $collections)
    {
        $this->dimension = $dimension;
        $this->columns[$dimension] = new DimensionValueStrategy($this->dimension);
        $this->columns[self::COUNT_COLUMN] = new CountStrategy($this->dimension);
        $this->collections = $collections;
    }

    public function addColumn($name, callable $procedure = null)
    {
        $this->columns[$name] = $procedure ?? new UniqueValuesStrategy($name, $this->dimension);

        return $this;
    }

    public function display($strLength = 60)
    {
        (new TableView(array_keys($this->columns), $this->toArray()))->display($strLength);
    }

    public function toArray()
    {
        $ret = [];
        foreach ($this->collections as $collection) {
            $row = [];
            foreach ($this->columns as $columnName => $procedure) {
                $row[$columnName] = $procedure($collection);
            }
            $ret[] = $row;
        }

        return $ret;
    }

    public function where($columnName, callable $procedure)
    {
        $collections = [];
        foreach ($this->collections as $collection) {
            $newCollection = $collection->filter($columnName, $procedure);

            if ($newCollection->count() > 0) {
                $collections[] = $newCollection;
                // For performance, cache dimension value.
                $newCollection->cacheColumnValues($this->dimension, $collection->columnValues($this->dimension));
            }
        }

        return new self($this->dimension, $collections);
    }

    public function getCollection($dimensionValue)
    {
        foreach ($this->collections as $collection) {
            if ($collection->columnValues($this->dimension) == $dimensionValue) {
                return $collection;
            }
        }

        return null;
    }

    public function count()
    {
        return count($this->collections);
    }

    public function itemCount()
    {
        $cnt = 0;

        foreach ($this->collections as $collection) {
            $cnt += $collection->count();
        }

        return $cnt;
    }
}
