<?php
declare(strict_types=1);

namespace LogAnalyzer;

use LogAnalyzer\Collection\DatabaseInterface;

class Collection implements \Countable, \IteratorAggregate
{
    protected $itemIds;
    protected $database;
    protected $cache = [];

    /**
     * @param int[] $itemIds
     * @param DatabaseInterface $database
     */
    public function __construct(array $itemIds, DatabaseInterface $database)
    {
        $this->itemIds = $itemIds;
        $this->database = $database;
    }

    public function count(): int
    {
        return count($this->itemIds);
    }

    public function dimension($columnName, callable $procedure = null): View
    {
        $collections = $this->groupBy($columnName, $procedure);
        foreach ($collections as $value => $collection) {
            // For performance, cache dimension value.
            $collection->cache($columnName, $value);
        }

        return new View(View::buildDimensionStrategy($columnName), array_values($collections));
    }

    /**
     * @param string $columnName
     * @param callable|null $procedure
     * @return Collection[]
     */
    public function groupBy($columnName, callable $procedure = null): array
    {
        $itemIdsByValue = [];
        foreach ($this->database->getSubset($this->itemIds, $columnName) as $value => $itemIds) {
            $calcValue = $this->calcValue($value, $procedure);
            $itemIdsByValue[$calcValue] = array_merge($itemIds, $itemIdsByValue[$calcValue] ?? []);
        }

        $collections = [];
        foreach ($itemIdsByValue as $value => $itemIds) {
            $collections[$value] = new self($itemIds, $this->database);
        }

        return $collections;
    }

    public function values($columnName)
    {
        if (isset($this->cache[$columnName])) {
            return $this->cache[$columnName];
        }

        $ret = [];
        foreach ($this->itemIds as $itemId) {
            if (!is_null($value = $this->database->getValue($itemId, $columnName))) {
                $ret[] = $value;
            }
        }

        return $ret;
    }

    public function filter($columnName, callable $procedure): self
    {
        $itemIds = [];
        foreach ($this->itemIds as $itemId) {
            if ($procedure($this->database->getValue($itemId, $columnName)) === true) {
                $itemIds[] = $itemId;
            }
        }

        return new self($itemIds, $this->database);
    }

    public function cache($columnName, $value): void
    {
        $this->cache[$columnName] = $value;
    }

    public function flush(): void
    {
        $this->cache = [];
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
