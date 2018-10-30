<?php
namespace LogAnalyzer\CollectionBuilder;

use LogAnalyzer\CollectionBuilder\Items\ItemInterface;
use LogAnalyzer\View;

class Collection implements \Countable, \IteratorAggregate
{
    /**
     * @var ItemInterface[]
     */
    private $items;

    /**
     * @param \LogAnalyzer\CollectionBuilder\Items\ItemInterface[] $items
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
     * @param callable $procedure
     * @return View
     */
    public function dimension($key, callable $procedure = null)
    {
        $progressBar = new View\ProgressBar($this->count());

        $dimensionItems = [];
        foreach ($this->items as $item) {
            if (!is_null($procedure)) {
                $dimensionValue = $procedure($item);
                $dimensionValue = is_null($dimensionValue) ? 'null' : $dimensionValue;
                $dimensionItems[$dimensionValue][] = $item;
            } elseif ($item->have($key)) {
                $dimensionValue = $item->get($key);
                $dimensionItems[$dimensionValue][] = $item;
            } else {
                $dimensionItems['null'][] = $item;
            }
        }

        $collection = [];
        foreach ($dimensionItems as $dimensionValue => $items) {
            $collection[$dimensionValue] = new self($items);
        }

        return new View($key, $collection);
    }

    /**
     * @param string|callable $procedure
     * @return array|mixed
     */
    public function sum($procedure)
    {
        if (is_callable($procedure)) {
            $ret = array_reduce($this->items, $procedure);
        } elseif (is_string($procedure)) {
            $ret = [];
            foreach ($this->items as $item) {
                if ($item->have($procedure)) {
                    $ret[] = $item->get($procedure);
                }
            }
        } else {
            throw new \InvalidArgumentException('$calc is not callable or string.');
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
        foreach ($this->items as $item) {
            if ($procedure($item) === true) {
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
