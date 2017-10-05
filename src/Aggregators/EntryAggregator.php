<?php
namespace LogAnalyzer\Aggregators;

use LogAnalyzer\Entries\EntryInterface;

class EntryAggregator
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

    /**
     * Build new aggregator on entries satisfied with callable
     * @param callable $callable
     * @return EntryAggregator
     */
    public function extract(callable $callable)
    {
        $entries = [];
        foreach ($this->entries as $entry) {
            if ($callable($entry) === true) {
                $entries = $entry;
            }
        }

        return new self($entries);
    }
}
