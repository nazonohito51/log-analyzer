<?php
namespace LogAnalyzer\Aggregators;

use LogAnalyzer\Entries\Entry;
use LogAnalyzer\Entries\EntryInterface;
use LogAnalyzer\TestCase;

class EntryAggregatorTest extends TestCase
{
    public function testExtract()
    {
        $aggregator = new EntryAggregator([
            new Entry(['key1' => 'value1', 'should_extract_key' => 1]),
            new Entry(['key2' => 'value2']),
            new Entry(['key3' => 'value3', 'should_extract_key' => 1]),
        ]);

        $new_aggregator = $aggregator->extract(function (EntryInterface $entry) {
            return $entry->haveProperty('should_extract_key');
        });

        $this->assertEquals(2, $new_aggregator->count());
    }
}
