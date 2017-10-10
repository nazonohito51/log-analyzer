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

    public function count()
    {
        return count($this->aggregates);
    }
}
