<?php
namespace Tests\Unit\LogAnalyzer\Database;

use LogAnalyzer\Database\InMemoryColumn;
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

        $column->add($addValue, $addId);

        $this->assertEquals($expected, $column->getItems($addValue));
    }

    public function testGetItems()
    {
        $column = new InMemoryColumn(['value1' => [1, 2, 3]]);

        $this->assertEquals([1, 2, 3], $column->getItems('value1'));
        $this->assertNull($column->getItems('value2'));
    }

    public function testGetValue()
    {
        $column = new InMemoryColumn(['value1' => [1, 2], 'value2' => [3]]);

        $this->assertEquals('value1', $column->getValue(1));
        $this->assertEquals('value1', $column->getValue(2));
        $this->assertEquals('value2', $column->getValue(3));
        $this->assertNull($column->getValue(4));
    }
}
