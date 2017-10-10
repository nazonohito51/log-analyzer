<?php
namespace LogAnalyzer\Aggregates;

use LogAnalyzer\Entries\EntryInterface;
use LucidFrame\Console\ConsoleTable;

class EntryAggregate
{
    /**
     * @var EntryInterface[]
     */
    private $entries;

    private $previousDimension = [];

    /**
     * @param EntryInterface[] $entries
     */
    public function __construct(array $entries)
    {
        $this->entries = $entries;
    }

    public function count()
    {
        return count($this->entries);
    }

    public function display($dimension_property)
    {
        $result = [];
        foreach ($this->entries as $entry) {
            if ($entry->haveProperty($dimension_property)) {
                $dimension_value = $entry->{$dimension_property};
                $result[$dimension_value][] = $entry;
            } else {
                $result['null'][] = $entry;
            }
        }

        $this->previousDimension = $result;
        $this->displayTable($dimension_property, $result);
    }

    private function displayTable($dimension_property, array $dimension_result)
    {
        $table = new ConsoleTable();
        $table->addHeader($dimension_property)
            ->addHeader('Count');

        foreach ($dimension_result as $property_value => $entries) {
            $table->addRow()->addColumn($property_value)->addColumn(count($entries));
        }
        $table->display();
    }

    /**
     * Build new aggregator on entries satisfied with callable
     * @param callable $callable
     * @return EntryAggregate
     */
    public function extract(callable $callable)
    {
        $entries = [];
        foreach ($this->entries as $entry) {
            if ($callable($entry) === true) {
                $entries[] = $entry;
            }
        }

        return new self($entries);
    }
}
