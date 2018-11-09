<?php
namespace LogAnalyzer;

use LogAnalyzer\Collection;
use LogAnalyzer\View\ColumnValueStrategy\AbstractStrategy;
use LogAnalyzer\View\ColumnValueStrategy\CountStrategy;
use LogAnalyzer\View\ColumnValueStrategy\DimensionValueStrategy;
use LogAnalyzer\View\ColumnValueStrategy\UniqueValuesStrategy;
use LucidFrame\Console\ConsoleTable;

class View implements \Countable
{
    const COUNT_COLUMN = '_count';

    protected $dimension;
    protected $columns;

    /**
     * @var Collection[]
     */
    private $collections;

    public function __construct($dimension, array $collections)
    {
        $this->dimension = $dimension;
        $this->columns[$dimension] = new DimensionValueStrategy();
        $this->columns[self::COUNT_COLUMN] = new CountStrategy();
        $this->collections = $collections;
    }

    public function addColumn($name, callable $procedure = null)
    {
        $this->columns[$name] = $procedure ?? new UniqueValuesStrategy($name);

        return $this;
    }

    public function display($strLength = 60)
    {
        $table = new ConsoleTable();

        foreach ($this->columns as $name => $procedure) {
            $table->addHeader($name);
        }
        foreach ($this->toArray() as $row) {
            $table->addRow();
            foreach ($this->columns as $name => $procedure) {
                $table->addColumn($this->formatColumnValue($row[$name], $strLength));
            }
        }

        $table->display();
        echo 'sum(' . self::COUNT_COLUMN . "): {$this->itemCount()}\n";
    }

    public function toArray()
    {
        $ret = [];
        foreach ($this->collections as $dimensionValue => $collection) {
            $row = [];
            foreach ($this->columns as $columnName => $procedure) {
                $row[$columnName] = $procedure($collection, $dimensionValue);
            }
            $ret[] = $row;
        }

        return $ret;
    }

    public function count()
    {
        return count($this->collections);
    }

    public function where($columnName, callable $procedure)
    {
        $collections = [];
        foreach ($this->collections as $value => $collection) {
            $newCollection = $collection->filter($columnName, $procedure);

            if ($newCollection->count() > 0) {
                $collections[$value] = $newCollection;
            }
        }

        return new self($this->dimension, $collections);
    }

    public function getCollection($dimensionValue)
    {
        return isset($this->collections[$dimensionValue]) ? $this->collections[$dimensionValue] : null;
    }

    /**
     * @param string|array $value
     * @param int $maxLength
     * @return string
     */
    protected function formatColumnValue($value, $maxLength = null)
    {
        if (is_array($value)) {
            if (count($value) > 1) {
                $value = '[' . implode(', ', $value) . ']';
            } else {
                $value = $value[0];
            }
        }

        if ($maxLength && strlen($value) > $maxLength) {
            $value = substr($value, 0, $maxLength) . '...';
        }

        return $value;
    }

    public function itemCount()
    {
        $cnt = 0;

        foreach ($this->collections as $dimensionValue => $collection) {
            $cnt += $collection->count();
        }

        return $cnt;
    }
}
