<?php
namespace LogAnalyzer\Aggregates;

use LogAnalyzer\Entries\EntryInterface;
use LogAnalyzer\View;

class EntryAggregate implements \Countable
{
    /**
     * @var EntryInterface[]
     */
    private $entries;

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
