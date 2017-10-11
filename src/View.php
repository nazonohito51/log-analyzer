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

    public function display(array $options = [])
    {
        $table = new ConsoleTable();
        $str_length = isset($options['length']) ? $options['length'] : null;
        $sort = isset($options['sort']) ? $options['sort'] : null;
        $where = isset($options['where']) ? $options['where'] : null;

        foreach ($this->columns as $column) {
            $table->addHeader($column);
        }
        foreach ($this->toArray($sort, $where) as $row) {
            $table->addRow();
            foreach ($this->columns as $column_name => $calc_column) {
                if (is_array($row[$column_name])) {
                    $column_value = implode(', ', $row[$column_name]);
                } else {
                    $column_value = $row[$column_name];
                }

                $trimmed_value = substr($column_value, 0 , $str_length);
                $trimmed_value .= ($column_value != $trimmed_value) ? '...' : '';
                $table->addColumn($trimmed_value);
            }
        }

        $table->display();
    }

    public function toArray(callable $sort = null, callable $where = null)
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
        return count($this->aggregates);
    }

    public function getAggregate($dimension_value)
    {
        return isset($this->aggregates[$dimension_value]) ? $this->aggregates[$dimension_value] : null;
    }
}
