<?php
declare(strict_types=1);

namespace LogAnalyzer;

use LogAnalyzer\View\AbstractColumnStrategy;
use LogAnalyzer\View\ColumnStrategyFactory;
use LogAnalyzer\View\CountStrategy;
use LogAnalyzer\View\DimensionStrategy;
use LogAnalyzer\Presenter\ConsoleTable;

class View implements \Countable
{
    /**
     * @var DimensionStrategy
     */
    protected $dimension;

    /**
     * @var AbstractColumnStrategy[]
     */
    protected $columnStrategies;

    /**
     * @var Collection[]
     */
    protected $collections;
    /**
     * @var ColumnStrategyFactory
     */
    protected $factory;

    /**
     * @param DimensionStrategy $dimension
     * @param Collection[] $collections
     * @param ColumnStrategyFactory|null $factory
     */
    public function __construct(DimensionStrategy $dimension, array $collections, ColumnStrategyFactory $factory = null)
    {
        $this->dimension = $dimension;
        $this->collections = $collections;
        $this->factory = $factory ?? $this->getDefaultStrategyFactory();
        $this->columnStrategies[] = $dimension;
        $this->columnStrategies[] = new CountStrategy();
    }

    public function getDefaultStrategyFactory()
    {
        return new ColumnStrategyFactory();
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
                $row[$strategy->name()] = $strategy($collection);
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
                $newCollection->cache($this->dimension->name(), $this->dimensionValueOf($collection));
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

    public function reset()
    {
        $this->columnStrategies = [$this->dimension, new CountStrategy()];
    }

    public function count(): int
    {
        return count($this->collections);
    }
}
