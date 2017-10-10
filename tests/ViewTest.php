<?php
namespace LogAnalyzer;

use LogAnalyzer\Aggregates\EntryAggregate;
use LogAnalyzer\Entries\Entry;
use LogAnalyzer\Entries\EntryInterface;

class ViewTest extends TestCase
{
    public function testToArray()
    {
        $view = new View('dimension_name', [
            'value1' => new EntryAggregate([
                new Entry(['dimension_name' => 'value1']),
                new Entry(['dimension_name' => 'value1']),
            ]),
            'value2' => new EntryAggregate([
                new Entry(['dimension_name' => 'value2'])
            ])
        ]);

        $array = $view->toArray();

        $this->assertEquals([['dimension_name' => ['value1']], ['dimension_name' => ['value2']]], $array);
    }

    public function testAddColumn()
    {
        $view = new View('dimension_name', [
            'value1' => new EntryAggregate([
                new Entry(['dimension_name' => 'value1', 'other_property' => '1']),
                new Entry(['dimension_name' => 'value1', 'other_property' => '2']),
            ]),
            'value2' => new EntryAggregate([
                new Entry(['dimension_name' => 'value2', 'other_property' => '3'])
            ])
        ]);

        $array = $view->addColumn('other_property')->toArray();

        $this->assertEquals(['1', '2'], $array[0]['other_property']);
        $this->assertEquals(['3'], $array[1]['other_property']);
    }

    public function testAddColumnByClosure()
    {
        $view = new View('dimension_name', [
            'value1' => new EntryAggregate([
                new Entry(['dimension_name' => 'value1', 'other_property' => '1']),
                new Entry(['dimension_name' => 'value1', 'other_property' => '2']),
            ]),
            'value2' => new EntryAggregate([
                new Entry(['dimension_name' => 'value2', 'other_property' => '6'])
            ])
        ]);

        $array = $view->addColumn('other_property', function ($carry, EntryInterface $entry) {
            $carry += $entry->other_property;
            return $carry;
        })->toArray();

        $this->assertEquals(3, $array[0]['other_property']);
        $this->assertEquals(6, $array[1]['other_property']);
    }
}
