<?php
declare(strict_types=1);

namespace LogAnalyzer;

use LogAnalyzer\View\AbstractColumnValueStrategy;
use LogAnalyzer\View\ColumnValueStrategyFactory;
use LogAnalyzer\View\ColumnValueStrategyInterface;
use LogAnalyzer\View\CountStrategy;
use LogAnalyzer\View\DimensionStrategy;
use LogAnalyzer\View\UniqueValuesStrategy;
use LogAnalyzer\Presenter\ConsoleTable;

class View implements \Countable
{
    /**
     * @var DimensionStrategy
     */
    protected $dimension;

    /**
     * @var AbstractColumnValueStrategy[]
     */
    protected $columnStrategies;

    /**
     * @var Collection[]
     */
    protected $collections;
    /**
     * @var ColumnValueStrategyFactory
     */
    protected $factory;

    /**
     * @param DimensionStrategy $dimension
     * @param Collection[] $collections
     * @param ColumnValueStrategyFactory|null $factory
     */
    public function __construct(DimensionStrategy $dimension, array $collections, ColumnValueStrategyFactory $factory = null)
    {
        $this->dimension = $dimension;
        $this->collections = $collections;
        $this->factory = $factory ?? $this->getDefaultStrategyFactory();
        $this->columnStrategies[] = $dimension;
        $this->columnStrategies[] = new CountStrategy();
    }

    public function getDefaultStrategyFactory()
    {
        return new ColumnValueStrategyFactory();
    }

    public static function buildDimensionStrategy($dimensionName)
    {
        return new DimensionStrategy($dimensionName);
    }

    public function addColumn(string $name, callable $procedure = null): self
    {
        $this->columnStrategies[] = $this->factory->build($name, $procedure);

        return $this;
    }

    public function table(): ConsoleTable
    {
        return new ConsoleTable($this->columnStrategies, $this->toArray());
    }

    public function toArray(): array
    {
        $ret = [];
        foreach ($this->collections as $collection) {
            $row = [];
            foreach ($this->columnStrategies as $strategy) {
                $row[$strategy->columnHeader()] = $strategy($collection);
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
                $newCollection->cache($this->dimension->columnHeader(), $this->dimensionValueOf($collection));
            }
        }

        return new self($this->dimension, $collections);
    }

    public function getCollection($dimensionValue): Collection
    {
        foreach ($this->collections as $collection) {
            if ($this->dimensionValueOf($collection) == $dimensionValue) {
                return $collection;
            }
        }

        return null;
    }

    protected function dimensionValueOf(Collection $collection)
    {
        return $this->dimension->__invoke($collection);
    }

    public function count(): int
    {
        return count($this->collections);
    }
}
