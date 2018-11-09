<?php
namespace LogAnalyzer;

use LogAnalyzer\Collection;
use LucidFrame\Console\ConsoleTable;

class View implements \Countable
{
    const COUNT_COLUMN = '_count';

    private $dimension;
    private $columns;

    /**
     * @var Collection[]
     */
    private $collections;

    public function __construct($dimension, array $collections)
    {
        $this->dimension = $dimension;
        $this->columns[$dimension] = $dimension;
        $this->columns['Count'] = self::COUNT_COLUMN;
        $this->collections = $collections;
    }

    public function addColumn($name, callable $procedure = null)
    {
        $this->columns[$name] = $procedure ?? function ($value) {return $value;};

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
                $value = $this->formatColumnValue($row[$name], $strLength);
                $table->addColumn($value);
            }
        }

        $table->display();
    }

    public function toArray()
    {
        $ret = [];
        foreach ($this->collections as $dimensionValue => $collection) {
            $row = [];
            foreach ($this->columns as $columnName => $procedure) {
                if ($columnName == $this->dimension) {
                    $row[$columnName] = $dimensionValue;
                } elseif ($procedure == self::COUNT_COLUMN) {
                    $row[$columnName] = $collection->count();
                } else {
                    $values = array_unique($collection->columnValues($columnName));
                    $row[$columnName] = array_map($procedure, $values);
                }
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
    private function formatColumnValue($value, $maxLength = null)
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
}
