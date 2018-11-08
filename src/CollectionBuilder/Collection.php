<?php
namespace LogAnalyzer\CollectionBuilder;

use LogAnalyzer\CollectionBuilder\Items\ItemInterface;
use LogAnalyzer\Database\Column\ColumnFactory;
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
     * @param string $columnName
     * @param callable $procedure
     * @return View
     */
    public function dimension($columnName, callable $procedure = null)
    {
//        $progressBar = new View\ProgressBar($this->count());

        $itemIdsByValue = [];
        foreach ($this->database->getColumnSubset($columnName, $this->itemIds) as $value => $itemIds) {
            if (!is_null($procedure)) {
                if (is_null($value = $procedure($value))) {
                    $value = 'null';
                }
            }
            $itemIdsByValue[$value] = array_merge($itemIds, $itemIdsByValue[$value] ?? []);
        }

        $collections = [];
        foreach ($itemIdsByValue as $value => $itemIds) {
            $collections[$value] = new self($itemIds, $this->database);
        }

        return new View($columnName, $collections);
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
