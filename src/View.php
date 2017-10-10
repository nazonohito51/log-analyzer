<?php
namespace LogAnalyzer;

use LogAnalyzer\Aggregates\EntryAggregate;
use LucidFrame\Console\ConsoleTable;

class View implements \Countable
{
    private $dimension;

    /**
     * @var EntryAggregate[]
     */
    private $aggregates;

    public function __construct($dimension, array $aggregates)
    {
        $this->dimension = $dimension;
        $this->aggregates = $aggregates;
    }

    public function display()
    {
        $table = new ConsoleTable();

        $table->addHeader($this->dimension);
        foreach ($this->aggregates as $dimension_value => $aggregate) {
            $table->addRow()->addColumn($dimension_value);
        }

        $table->display();
    }

    public function toArray()
    {
        $ret = [];
        foreach ($this->aggregates as $dimension_value => $aggregate) {
            $ret[$dimension_value] = [$this->dimension => $dimension_value];
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
