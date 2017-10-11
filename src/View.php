<?php
namespace LogAnalyzer;

use LogAnalyzer\Aggregates\EntryAggregate;
use LucidFrame\Console\ConsoleTable;

class View implements \Countable
{
    const COUNT_COLUMN = '_count';

    private $dimension;
    private $columns;

    /**
     * @var EntryAggregate[]
     */
    private $aggregates;

    public function __construct($dimension, array $aggregates)
    {
        $this->dimension = $dimension;
        $this->columns[$dimension] = $dimension;
        $this->columns['Count'] = self::COUNT_COLUMN;
        $this->aggregates = $aggregates;
    }

    public function addColumn($column_name, callable $calc_column = null)
    {
        $this->columns[$column_name] = !is_null($calc_column) ? $calc_column : $column_name;

        return $this;
    }

    public function display()
    {
        $table = new ConsoleTable();

        foreach ($this->columns as $column) {
            $table->addHeader($column);
        }
        foreach ($this->toArray() as $row) {
            $table->addRow();
            foreach ($this->columns as $column_name => $calc_column) {
                if (is_array($row[$column_name])) {
                    $table->addColumn(implode(', ', $row[$column_name]));
                } else {
                    $table->addColumn($row[$column_name]);
                }
            }
        }

        $table->display();
    }

    public function toArray()
    {
        $ret = [];
        foreach ($this->aggregates as $dimension_value => $aggregate) {
            $row = [];
            foreach ($this->columns as $column_name => $calc_column) {
                if ($column_name == $this->dimension) {
                    $row[$column_name] = $dimension_value;
                } elseif ($calc_column == self::COUNT_COLUMN) {
                    $row[$column_name] = count($aggregate);
                } elseif (is_callable($calc_column)) {
                    $row[$column_name] = $aggregate->sum($calc_column);
                } else {
                    $row[$column_name] = array_unique($aggregate->sum($calc_column));
                }
            }
            $ret[] = $row;
        }

        return $ret;
    }

    public function count()
    {
        return count($this->aggregates);
    }

    public function getAggregate($dimension_value)
    {
        return isset($this->aggregates[$dimension_value]) ? $this->aggregates[$dimension_value] : null;
    }
}
