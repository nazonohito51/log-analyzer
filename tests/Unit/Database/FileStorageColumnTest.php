<?php
namespace Tests\Unit\LogAnalyzer\Database;

use LogAnalyzer\Database\FileStorageColumn;
use Tests\LogAnalyzer\TestCase;

class FileStorageColumnTest extends TestCase
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
        $column = new FileStorageColumn($this->getTmpDir(), $initial);

        $column->add($addValue, $addId);

        $this->assertEquals($expected, $column->getItems($addValue));
    }

    public function testGetItems()
    {
        $column = new FileStorageColumn($this->getTmpDir(), ['value1' => [1, 2, 3]]);

        $this->assertEquals([1, 2, 3], $column->getItems('value1'));
        $this->assertEquals([], $column->getItems('value2'));
    }

    public function testGetValue()
    {
        $column = new FileStorageColumn($this->getTmpDir(), ['value1' => [1, 2], 'value2' => [3]]);

        $this->assertEquals('value1', $column->getValue(1));
        $this->assertEquals('value1', $column->getValue(2));
        $this->assertEquals('value2', $column->getValue(3));
        $this->assertNull($column->getValue(4));
    }

    public function testGetValues()
    {
        $column = new FileStorageColumn($this->getTmpDir(), ['value1' => [1, 2], 'value2' => [3]]);

        $this->assertEquals(['value1', 'value2'], $column->getValues());
    }

    public function testGetSubset()
    {
        $column = new FileStorageColumn($this->getTmpDir(), ['value1' => [1, 2], 'value2' => [3, 4], 'value3' => [5, 6]]);

        $this->assertEquals(['value1' => [1, 2], 'value3' => [5]], $column->getSubset([1, 2, 5, 7]));
    }

    public function testSave()
    {
        // TODO: fix docker settings
        $this->markTestSkipped();
    }
}