<?php
namespace LogAnalyzer\Aggregates;

use LogAnalyzer\Entries\Entry;
use LogAnalyzer\Entries\EntryInterface;
use LogAnalyzer\TestCase;

class EntryAggregateTest extends TestCase
{
    public function testExtract()
    {
        $aggregator = new EntryAggregate([
            new Entry(['key' => 'value1', 'should_extract_key' => 1]),
            new Entry(['key' => 'value2']),
            new Entry(['key' => 'value3', 'should_extract_key' => 1]),
        ]);

        $new_aggregator = $aggregator->extract(function (EntryInterface $entry) {
            return $entry->haveProperty('should_extract_key');
        });
        $this->assertEquals(2, $new_aggregator->count());
    }
}