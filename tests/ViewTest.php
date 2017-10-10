<?php
namespace LogAnalyzer;

use LogAnalyzer\Aggregates\EntryAggregate;
use LogAnalyzer\Entries\Entry;

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

        $this->assertEquals(['value1' => [], 'value2' => []], $array);
    }

//    public function testAddColumn()
//    {
//        $view = new View('dimension_name', [
//            'value1' => new EntryAggregate([
//                new Entry(['dimension_name' => 'value1', 'other_property' => '1']),
//                new Entry(['dimension_name' => 'value1', 'other_property' => '2']),
//            ]),
//            'value2' => new EntryAggregate([
//                new Entry(['dimension_name' => 'value2', 'other_property' => '3'])
//            ])
//        ]);
//
//        $array = $view->addColumn('other_property')->toArray();
//
//        $this->assertEquals(['1', '2'], $array[0]['other_property']);
//        $this->assertEquals('3', $array[0]['other_property']);
//    }
}
