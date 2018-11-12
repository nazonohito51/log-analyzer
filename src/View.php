<?php
declare(strict_types=1);

namespace LogAnalyzer;

use LogAnalyzer\Collection;
use LogAnalyzer\View\AbstractStrategy;
use LogAnalyzer\View\CountStrategy;
use LogAnalyzer\View\DimensionValueStrategy;
use LogAnalyzer\View\UniqueValuesStrategy;
use LogAnalyzer\Presenter\TableView;
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

    public function addColumn($name, callable $procedure = null): self
    {
        $this->columns[$name] = $procedure ?? new UniqueValuesStrategy($name, $this->dimension);

        return $this;
    }

    public function table(): TableView
    {
        return new TableView(array_keys($this->columns), $this->toArray());
    }

    public function toArray(): array
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

    public function where($columnName, callable $procedure): self
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

    public function getCollection($dimensionValue): Collection
    {
        foreach ($this->collections as $collection) {
            if ($collection->columnValues($this->dimension) == $dimensionValue) {
                return $collection;
            }
        }

        return null;
    }

    public function count(): int
    {
        return count($this->collections);
    }
}
