<?php
namespace LogAnalyzer\Aggregates;

use LogAnalyzer\Entries\EntryInterface;
use LogAnalyzer\View;
use LucidFrame\Console\ConsoleTable;

class EntryAggregate implements \Countable
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

    public function dimension($key)
    {
        $dimension_entries = [];
        foreach ($this->entries as $entry) {
            if ($entry->haveProperty($key)) {
                $dimension_value = $entry->{$key};
                $dimension_entries[$dimension_value][] = $entry;
            } else {
                $dimension_entries['null'][] = $entry;
            }
        }

        $aggregates = [];
        foreach ($dimension_entries as $dimension_value => $entries) {
            $aggregates[$dimension_value] = new self($entries);
        }

        return new View($key, $aggregates);
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
