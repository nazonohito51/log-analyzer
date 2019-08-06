<?php
declare(strict_types=1);

namespace Tests\Unit\LogAnalyzer;

use LogAnalyzer\Collection;
use LogAnalyzer\CollectionBuilder\Items\Item;
use LogAnalyzer\CollectionBuilder\Items\ItemInterface;
use LogAnalyzer\Collection\DatabaseInterface;
use LogAnalyzer\View;
use Tests\LogAnalyzer\TestCase;

class ViewTest extends TestCase
{
    public function testBuildDimensionStrategy()
    {
        $strategy = View::buildDimensionStrategy('dimensionName');

        $this->assertInstanceOf(View\DimensionStrategy::class, $strategy);
        $this->assertEquals('dimensionName', $strategy->name());
    }

    public function testToArray()
    {
        $collectionValue1 = $this->createMock(Collection::class);
        $collectionValue1->method('values')->willReturn('value1');
        $collectionValue1->method('count')->willReturn(2);
        $collectionValue2 = $this->createMock(Collection::class);
        $collectionValue2->method('values')->willReturn('value2');
        $collectionValue2->method('count')->willReturn(1);
        $strategy = $this->createMock(View\DimensionStrategy::class);
        $strategy->method('name')->willReturn('dimension_value');
        $strategy->method('__invoke')->willReturnMap([
            [$collectionValue1, 'value1'],
            [$collectionValue2, 'value2'],
        ]);
        $view = new View($strategy, [$collectionValue1, $collectionValue2]);

        $array = $view->toArray();

        $this->assertArraySubset([
            ['dimension_value' => 'value1', '_count' => 2],
            ['dimension_value' => 'value2', '_count' => 1]
        ], $array);
    }

    public function testToArrayUsingWhere()
    {
        $closure = function ($value) {
            return $value > 100;
        };
        $newCollectionValue1 = $this->createMock(Collection::class);
        $newCollectionValue1->method('values')->willReturn('value1');
        $newCollectionValue1->method('count')->willReturn(2);
        $newCollectionValue2 = $this->createMock(Collection::class);
        $newCollectionValue2->method('values')->willReturn('value2');
        $newCollectionValue2->method('count')->willReturn(1);
        $collectionValue1 = $this->createMock(Collection::class);
        $collectionValue1->method('filter')->with('column', $closure)->willReturn($newCollectionValue1);
        $collectionValue2 = $this->createMock(Collection::class);
        $collectionValue2->method('filter')->with('column', $closure)->willReturn($newCollectionValue2);
        $strategy = $this->createMock(View\DimensionStrategy::class);
        $strategy->method('name')->willReturn('column');
        $strategy->method('__invoke')->willReturnMap([
            [$newCollectionValue1, 'value1'],
            [$newCollectionValue2, 'value2'],
        ]);
        $view = new View($strategy, [$collectionValue1, $collectionValue2]);

        $newView = $view->where('column', $closure)->toArray();

        $this->assertEquals([
            ['column' => 'value1', '_count' => 2],
            ['column' => 'value2', '_count' => 1],
        ], $newView);
    }

    public function testToArrayUsingHaveCount()
    {
        $collectionValue1 = $this->createMock(Collection::class);
        $collectionValue1->method('values')->willReturn('value1');
        $collectionValue1->method('count')->willReturn(2);
        $collectionValue2 = $this->createMock(Collection::class);
        $collectionValue2->method('values')->willReturn('value1');
        $collectionValue2->method('count')->willReturn(1);
        $strategy = $this->createMock(View\DimensionStrategy::class);
        $strategy->method('name')->willReturn('column');
        $strategy->method('__invoke')->willReturnMap([
            [$collectionValue1, 'value1'],
            [$collectionValue2, 'value2'],
        ]);
        $view = new View($strategy, [$collectionValue1, $collectionValue2]);

        $newView = $view->haveCount(2)->toArray();

        $this->assertEquals([
            ['column' => 'value1', '_count' => 2],
        ], $newView);
    }

    public function testAddColumn()
    {
        $collectionValue1 = $this->createMock(Collection::class);
        $collectionValue1->method('count')->willReturn(2);
        $collectionValue1->method('values')->willReturn([100, 200]);
        $collectionValue2 = $this->createMock(Collection::class);
        $collectionValue2->method('count')->willReturn(1);
        $collectionValue2->method('values')->willReturn([300]);
        $view = new View(View::buildDimensionStrategy('dimensionName'), [$collectionValue1, $collectionValue2]);

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
        $collectionValue1->method('values')->willReturnMap([
            ['column1', [1, 2, 3]],
            ['column2', [4, 5, 6]],
        ]);
        $collectionValue2 = $this->createMock(Collection::class);
        $collectionValue2->method('count')->willReturn(1);
        $collectionValue2->method('values')->willReturnMap([
            ['column1', [100, 400]],
            ['column2', [200, 300]],
        ]);
        $view = new View(View::buildDimensionStrategy('dimensionName'), [
            'value1' => $collectionValue1,
            'value2' => $collectionValue2
        ]);

        $array = $view->addColumn('my_column', function (Collection $collection) {
            $column1Values = $collection->values('column1');
            $column2Values = $collection->values('column2');
            return max(array_merge($column1Values, $column2Values));
        })->toArray();

        $this->assertEquals([6, 400], array_column($array, 'my_column'));
    }
}
