<?php
namespace Tests\Unit\LogAnalyzer;

use LogAnalyzer\CollectionBuilder\Collection;
use LogAnalyzer\CollectionBuilder\Items\Item;
use LogAnalyzer\CollectionBuilder\Items\ItemInterface;
use LogAnalyzer\View;
use Tests\LogAnalyzer\TestCase;

class ViewTest extends TestCase
{
    public function testToArray()
    {
        $file = $this->getLogFileMock([
            'dimension_name:value2',
            'dimension_name:value1',
            'dimension_name:value1',
        ]);
        $view = new View('dimension_name', [
            'value1' => new Collection([
                new Item($file, 1),
                new Item($file, 2),
            ]),
            'value2' => new Collection([
                new Item($file, 0)
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
        $file = $this->getLogFileMock([
            'dimension_name:have_one',
            'dimension_name:have_three',
            'dimension_name:have_two',
            'dimension_name:have_three',
            'dimension_name:have_two',
            'dimension_name:have_three',
        ]);
        $view = new View('dimension_name', [
            'have_one' => new Collection([
                new Item($file, 0)
            ]),
            'have_three' => new Collection([
                new Item($file, 1),
                new Item($file, 3),
                new Item($file, 5),
            ]),
            'have_two' => new Collection([
                new Item($file, 2),
                new Item($file, 4),
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
        $file = $this->getLogFileMock([
            'dimension_name:have_one',
            'dimension_name:have_three',
            'dimension_name:have_two',
            'dimension_name:have_three',
            'dimension_name:have_two',
            'dimension_name:have_three',
        ]);
        $view = new View('dimension_name', [
            'have_one' => new Collection([
                new Item($file, 0)
            ]),
            'have_two' => new Collection([
                new Item($file, 2),
                new Item($file, 4),
            ]),
            'have_three' => new Collection([
                new Item($file, 1),
                new Item($file, 3),
                new Item($file, 5),
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
        $file = $this->getLogFileMock([
            "dimension_name:value1\tother_property:1",
            "dimension_name:value1\tother_property:2",
            "dimension_name:value2\tother_property:3",
        ]);
        $view = new View('dimension_name', [
            'value1' => new Collection([
                new Item($file, 0),
                new Item($file, 1),
            ]),
            'value2' => new Collection([
                new Item($file, 2)
            ])
        ]);

        $array = $view->addColumn('other_property')->toArray();

        $this->assertEquals(['1', '2'], $array[0]['other_property']);
        $this->assertEquals(['3'], $array[1]['other_property']);
    }

    public function testAddColumnByClosure()
    {
        $file = $this->getLogFileMock([
            "dimension_name:value1\tother_property:1",
            "dimension_name:value1\tother_property:2",
            "dimension_name:value2\tother_property:6",
        ]);
        $view = new View('dimension_name', [
            'value1' => new Collection([
                new Item($file, 0),
                new Item($file, 1),
            ]),
            'value2' => new Collection([
                new Item($file, 2)
            ])
        ]);

        $array = $view->addColumn('other_property', function ($carry, ItemInterface $item) {
            $carry += $item->get('other_property');
            return $carry;
        })->toArray();

        $this->assertEquals(3, $array[0]['other_property']);
        $this->assertEquals(6, $array[1]['other_property']);
    }
}
