<?php
namespace LogAnalyzer;

use LogAnalyzer\Aggregates\Collection;
use LogAnalyzer\Entries\Item;
use LogAnalyzer\Entries\EntryInterface;

class ViewTest extends TestCase
{
    public function testToArray()
    {
        $view = new View('dimension_name', [
            'value1' => new Collection([
                new Item(['dimension_name' => 'value1']),
                new Item(['dimension_name' => 'value1']),
            ]),
            'value2' => new Collection([
                new Item(['dimension_name' => 'value2'])
            ])
        ]);

        $array = $view->toArray();

        $this->assertEquals([
            ['dimension_name' => 'value1', 'Count' => 2],
            ['dimension_name' => 'value2', 'Count' => 1]
        ], $array);
    }

    public function testToArrayUsingSort()
    {
        $view = new View('dimension_name', [
            'have_one' => new Collection([
                new Item(['dimension_name' => 'have_one'])
            ]),
            'have_three' => new Collection([
                new Item(['dimension_name' => 'have_three']),
                new Item(['dimension_name' => 'have_three']),
                new Item(['dimension_name' => 'have_three']),
            ]),
            'have_two' => new Collection([
                new Item(['dimension_name' => 'have_two']),
                new Item(['dimension_name' => 'have_two']),
            ]),
        ]);

        $array = $view->toArray(function ($a, $b) {
            if ($a['Count'] == $b['Count']) {
                return 0;
            }

            return ($a['Count'] < $b['Count']) ? 1 : -1;
        });

        $this->assertEquals([
            ['dimension_name' => 'have_three', 'Count' => 3],
            ['dimension_name' => 'have_two', 'Count' => 2],
            ['dimension_name' => 'have_one', 'Count' => 1]
        ], $array);
    }

    public function testToArrayUsingWhere()
    {
        $view = new View('dimension_name', [
            'have_one' => new Collection([
                new Item(['dimension_name' => 'have_one'])
            ]),
            'have_two' => new Collection([
                new Item(['dimension_name' => 'have_two']),
                new Item(['dimension_name' => 'have_two']),
            ]),
            'have_three' => new Collection([
                new Item(['dimension_name' => 'have_three']),
                new Item(['dimension_name' => 'have_three']),
                new Item(['dimension_name' => 'have_three']),
            ]),
        ]);

        $array = $view->toArray(null, function ($row) {
            return ($row['Count'] >= 2);
        });

        $this->assertEquals([
            ['dimension_name' => 'have_two', 'Count' => 2],
            ['dimension_name' => 'have_three', 'Count' => 3],
        ], $array);
    }

    public function testAddColumn()
    {
        $view = new View('dimension_name', [
            'value1' => new Collection([
                new Item(['dimension_name' => 'value1', 'other_property' => '1']),
                new Item(['dimension_name' => 'value1', 'other_property' => '2']),
            ]),
            'value2' => new Collection([
                new Item(['dimension_name' => 'value2', 'other_property' => '3'])
            ])
        ]);

        $array = $view->addColumn('other_property')->toArray();

        $this->assertEquals(['1', '2'], $array[0]['other_property']);
        $this->assertEquals(['3'], $array[1]['other_property']);
    }

    public function testAddColumnByClosure()
    {
        $view = new View('dimension_name', [
            'value1' => new Collection([
                new Item(['dimension_name' => 'value1', 'other_property' => '1']),
                new Item(['dimension_name' => 'value1', 'other_property' => '2']),
            ]),
            'value2' => new Collection([
                new Item(['dimension_name' => 'value2', 'other_property' => '6'])
            ])
        ]);

        $array = $view->addColumn('other_property', function ($carry, EntryInterface $entry) {
            $carry += $entry->get('other_property');
            return $carry;
        })->toArray();

        $this->assertEquals(3, $array[0]['other_property']);
        $this->assertEquals(6, $array[1]['other_property']);
    }
}
