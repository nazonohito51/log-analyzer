<?php
namespace LogAnalyzer;

use LogAnalyzer\CollectionBuilder\Collection;
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
        $this->columns[$name] = !is_null($procedure) ? $procedure : $name;

        return $this;
    }

    public function display(array $options = [])
    {
        $table = new ConsoleTable();
        $str_length = isset($options['length']) ? $options['length'] : null;
        $sort = isset($options['sort']) ? $options['sort'] : null;
        $where = isset($options['where']) ? $options['where'] : null;

        foreach ($this->columns as $name => $procedure) {
            $table->addHeader($name);
        }
        foreach ($this->toArray($sort, $where) as $row) {
            $table->addRow();
            foreach ($this->columns as $name => $procedure) {
                $value = $this->formatColumnValue($row[$name], $str_length);
                $table->addColumn($value);
            }
        }

        $table->display();
    }

    public function toArray(callable $sort = null, callable $where = null)
    {
        $ret = [];
        foreach ($this->collections as $dimensionValue => $collection) {
            $row = [];
            foreach ($this->columns as $columnName => $procedure) {
                if ($columnName == $this->dimension) {
                    $row[$columnName] = $dimensionValue;
                } elseif ($procedure == self::COUNT_COLUMN) {
                    $row[$columnName] = $collection->count();
                } elseif (is_callable($procedure)) {
                    throw new \LogicException();
//                    $row[$columnName] = $collection->map('', $procedure);
                } else {
                    $row[$columnName] = array_unique($collection->columnValues($procedure));
                }
            }
            $ret[] = $row;
        }

        if ($where) {
            // array_values will number index again.
            $ret = array_values(array_filter($ret, $where));
        }
        if ($sort) {
            usort($ret, $sort);
        }

        return $ret;
    }

    public function count()
    {
        return count($this->collections);
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
