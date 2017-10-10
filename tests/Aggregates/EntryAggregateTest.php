<?php
namespace LogAnalyzer\Aggregates;

use LogAnalyzer\Entries\Entry;
use LogAnalyzer\Entries\EntryInterface;
use LogAnalyzer\TestCase;

class EntryAggregateTest extends TestCase
{
    public function testDimension()
    {
        $aggregate = new EntryAggregate([
            new Entry(['column' => 'value1']),
            new Entry(['column' => 'value1']),
            new Entry(['column' => 'value2']),
        ]);

        $this->assertEquals(2, $aggregate->dimension('column')->count());
    }

    public function testExtract()
    {
        $aggregate = new EntryAggregate([
            new Entry(['column' => 'value1', 'should_extract_key' => 1]),
            new Entry(['column' => 'value2']),
            new Entry(['column' => 'value3', 'should_extract_key' => 1]),
        ]);

        $new_aggregate = $aggregate->extract(function (EntryInterface $entry) {
            return $entry->haveProperty('should_extract_key');
        });
        $this->assertEquals(2, $new_aggregate->count());
    }
}
