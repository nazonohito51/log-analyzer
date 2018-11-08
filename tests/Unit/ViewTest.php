<?php
namespace Tests\Unit\LogAnalyzer;

use LogAnalyzer\CollectionBuilder\Collection;
use LogAnalyzer\CollectionBuilder\Items\Item;
use LogAnalyzer\CollectionBuilder\Items\ItemInterface;
use LogAnalyzer\Database\DatabaseInterface;
use LogAnalyzer\View;
use Tests\LogAnalyzer\TestCase;

class ViewTest extends TestCase
{
    public function testToArray()
    {
        $collectionValue1 = $this->createMock(Collection::class);
        $collectionValue1->method('count')->willReturn(2);
        $collectionValue2 = $this->createMock(Collection::class);
        $collectionValue2->method('count')->willReturn(1);
        $view = new View('dimension_name', [
            'value1' => $collectionValue1,
            'value2' => $collectionValue2
        ]);

        $array = $view->toArray();

        $this->assertEquals([
            ['dimension_name' => 'value1', 'Count' => 2],
            ['dimension_name' => 'value2', 'Count' => 1]
        ], $array);
    }

    public function testToArrayUsingWhere()
    {
        $closure = function ($value) {
            return $value > 100;
        };
        $newCollectionValue1 = $this->createMock(Collection::class);
        $newCollectionValue1->method('count')->willReturn(2);
        $newCollectionValue2 = $this->createMock(Collection::class);
        $newCollectionValue2->method('count')->willReturn(1);
        $collectionValue1 = $this->createMock(Collection::class);
        $collectionValue1->method('filter')->with('column', $closure)->willReturn($newCollectionValue1);
        $collectionValue2 = $this->createMock(Collection::class);
        $collectionValue2->method('filter')->with('column', $closure)->willReturn($newCollectionValue2);
        $view = new View('column', [
            'value1' => $collectionValue1,
            'value2' => $collectionValue2
        ]);

        $newView = $view->where('column', $closure)->toArray();

        $this->assertEquals([
            ['column' => 'value1', 'Count' => 2],
            ['column' => 'value2', 'Count' => 1],
        ], $newView);
    }

    public function testAddColumn()
    {
        $collectionValue1 = $this->createMock(Collection::class);
        $collectionValue1->method('count')->willReturn(2);
        $collectionValue1->method('columnValues')->willReturn([100, 200]);
        $collectionValue2 = $this->createMock(Collection::class);
        $collectionValue2->method('count')->willReturn(1);
        $collectionValue2->method('columnValues')->willReturn([300]);
        $view = new View('dimension_name', [
            'value1' => $collectionValue1,
            'value2' => $collectionValue2
        ]);

        $array = $view->addColumn('other_property')->toArray();

        $this->assertEquals([
            [100, 200],
            [300]
        ], array_column($array, 'other_property'));
    }

    public function testAddColumnByClosure()
    {
        $collectionValue1 = $this->createMock(Collection::class);
        $collectionValue1->method('count')->willReturn(2);
        $collectionValue1->method('columnValues')->willReturn([100, 200]);
        $collectionValue2 = $this->createMock(Collection::class);
        $collectionValue2->method('count')->willReturn(1);
        $collectionValue2->method('columnValues')->willReturn([300]);
        $view = new View('dimension_name', [
            'value1' => $collectionValue1,
            'value2' => $collectionValue2
        ]);

        $array = $view->addColumn('other_property', function ($value) {
            return $value * 100;
        })->toArray();

        $this->assertEquals([
            [10000, 20000],
            [30000]
        ], array_column($array, 'other_property'));
    }
}
