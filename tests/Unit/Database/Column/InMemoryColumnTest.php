<?php
declare(strict_types=1);

namespace Tests\Unit\LogAnalyzer\Database\Column;

use LogAnalyzer\Collection\Column\InMemoryColumn;
use Tests\LogAnalyzer\TestCase;

class InMemoryColumnTest extends TestCase
{
    public function providerAdd()
    {
        return [
            [[], 'value1', 1, [1],],
            [['value1' => [1, 2, 3]], 'value1', 4, [1, 2, 3, 4]],
            [['value2' => [1, 2, 3]], 'value1', 4, [4]]
        ];
    }

    /**
     * @param $initial
     * @param $addValue
     * @param $addId
     * @param $expected
     * @dataProvider providerAdd
     */
    public function testAdd($initial, $addValue, $addId, $expected)
    {
        $column = new InMemoryColumn($initial);

        $column->add($addId, $addValue);

        $this->assertEquals($expected, $column->getItemIds($addValue));
    }

    public function testGetItems()
    {
        $column = new InMemoryColumn(['value1' => [1, 2, 3]]);

        $this->assertEquals([1, 2, 3], $column->getItemIds('value1'));
        $this->assertEquals([], $column->getItemIds('value2'));
    }

    public function testGetValue()
    {
        $column = new InMemoryColumn(['value1' => [1, 2], 'value2' => [3]]);

        $this->assertEquals('value1', $column->getValue(1));
        $this->assertEquals('value1', $column->getValue(2));
        $this->assertEquals('value2', $column->getValue(3));
        $this->assertNull($column->getValue(4));
    }

    public function testGetValues()
    {
        $column = new InMemoryColumn(['value1' => [1, 2], 'value2' => [3]]);

        $this->assertEquals(['value1', 'value2'], $column->getValues());
    }

    public function testGetSubset()
    {
        $column = new InMemoryColumn(['value1' => [1, 2], 'value2' => [3, 4], 'value3' => [5, 6]]);

        $this->assertEquals(['value1' => [1, 2], 'value3' => [5]], $column->getSubset([1, 2, 5, 7]));
    }

    public function testSave()
    {
        $column = new InMemoryColumn();

        $this->assertTrue($column->save());
    }

    public function testDelete()
    {
        $column = new InMemoryColumn();

        $this->assertTrue($column->delete());
    }
}
