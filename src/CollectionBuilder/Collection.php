<?php
namespace LogAnalyzer\CollectionBuilder;

use LogAnalyzer\CollectionBuilder\Items\ItemInterface;
use LogAnalyzer\Database\ColumnFactory;
use LogAnalyzer\Database\DatabaseInterface;
use LogAnalyzer\Database\ColumnarDatabase;
use LogAnalyzer\View;

class Collection implements \Countable, \IteratorAggregate
{
    /**
     * @var ItemInterface[]
     */
    private $itemIds;
    /**
     * @var DatabaseInterface
     */
    private $database;

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

    /**
     * @param string $key
     * @param callable $procedure
     * @return View
     */
    public function dimension($key, callable $procedure = null)
    {
//        $progressBar = new View\ProgressBar($this->count());

//        $dimensionItems = [];
//        foreach ($this->items as $item) {
//            if (!is_null($procedure)) {
//                $dimensionValue = $procedure($item);
//                $dimensionValue = is_null($dimensionValue) ? 'null' : $dimensionValue;
//                $dimensionItems[$dimensionValue][] = $item;
//            } elseif ($item->have($key)) {
//                $dimensionValue = $item->get($key);
//                $dimensionItems[$dimensionValue][] = $item;
//            } else {
//                $dimensionItems['null'][] = $item;
//            }
//
//            $progressBar->update($item->getLogFile(), $item->getLinePos());
//        }

        $collection = [];
        foreach ($this->database->getColumnValues($key) as $value) {
            $itemIds = array_intersect($this->itemIds, $this->database->getItemIds($key, $value));

            if (count($itemIds) > 0) {
                $collection[$value] = new self($itemIds, $this->database);
            }
        }

//        $collection = [];
//        foreach ($dimensionItems as $dimensionValue => $items) {
//            $collection[$dimensionValue] = new self($items);
//        }

        return new View($key, $collection);
    }

    /**
     * @param string|callable $procedure
     * @return array|mixed
     */
    public function sum($procedure)
    {
        if (is_callable($procedure)) {
            $ret = array_reduce($this->itemIds, $procedure);
        } elseif (is_string($procedure)) {
            $ret = [];
            foreach ($this->itemIds as $itemId) {
                $ret[] = $this->database->getValue($procedure, $itemId);
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
        foreach ($this->itemIds as $item) {
            if ($procedure($item) === true) {
                $items[] = $item;
            }
        }

        return new self($items);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->itemIds);
    }
}
