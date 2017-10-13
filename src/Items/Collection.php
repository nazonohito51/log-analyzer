<?php
namespace LogAnalyzer\Items;

use LogAnalyzer\Items\ItemInterface;
use LogAnalyzer\View;

class Collection implements \Countable, \IteratorAggregate
{
    /**
     * @var ItemInterface[]
     */
    private $items;

    /**
     * @param \LogAnalyzer\Items\ItemInterface[] $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function count()
    {
        return count($this->items);
    }

    /**
     * @param string $key
     * @param callable $calc_dimension
     * @return View
     */
    public function dimension($key, callable $calc_dimension = null)
    {
        $dimension_items = [];
        foreach ($this->items as $item) {
            if (!is_null($calc_dimension)) {
                $dimension_value = $calc_dimension($item);
                $dimension_value = is_null($dimension_value) ? 'null' : $dimension_value;
                $dimension_items[$dimension_value][] = $item;
            } elseif ($item->have($key)) {
                $dimension_value = $item->get($key);
                $dimension_items[$dimension_value][] = $item;
            } else {
                $dimension_items['null'][] = $item;
            }
        }

        $collection = [];
        foreach ($dimension_items as $dimension_value => $items) {
            $collection[$dimension_value] = new self($items);
        }

        return new View($key, $collection);
    }

    public function sum($calc)
    {
        if (is_callable($calc)) {
            $ret = array_reduce($this->items, $calc);
        } elseif (is_string($calc)) {
            $ret = [];
            foreach ($this->items as $item) {
                if ($item->have($calc)) {
                    $ret[] = $item->get($calc);
                }
            }
        } else {
            throw new \InvalidArgumentException('$calc is not callable or string.');
        }

        return $ret;
    }

    /**
     * Build new collection on items satisfied with callable
     * @param callable $callable
     * @return Collection
     */
    public function filter(callable $callable)
    {
        $items = [];
        foreach ($this->items as $item) {
            if ($callable($item) === true) {
                $items[] = $item;
            }
        }

        return new self($items);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }
}
