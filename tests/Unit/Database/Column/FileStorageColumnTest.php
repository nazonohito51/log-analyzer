<?php
namespace Tests\Unit\LogAnalyzer\Database\Column;

use LogAnalyzer\Database\Column\FileStorageColumn;
use LogAnalyzer\Database\Column\FileStorageColumn\ValueStore;
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
        $column = new FileStorageColumn($this->getTmpDir(), $this->getValueStoreMock(), $initial);

        $column->add($addValue, $addId);

        $this->assertEquals($expected, $column->getItems($addValue));
    }

    public function testGetItems()
    {
        $column = new FileStorageColumn($this->getTmpDir(), $this->getValueStoreMock(), ['value1' => [1, 2, 3]]);

        $this->assertEquals([1, 2, 3], $column->getItems('value1'));
        $this->assertEquals([], $column->getItems('value2'));
    }

    public function testGetValue()
    {
        $column = new FileStorageColumn($this->getTmpDir(), $this->getValueStoreMock(), ['value1' => [1, 2], 'value2' => [3]]);

        $this->assertEquals('value1', $column->getValue(1));
        $this->assertEquals('value1', $column->getValue(2));
        $this->assertEquals('value2', $column->getValue(3));
        $this->assertNull($column->getValue(4));
    }

    public function testGetValues()
    {
        $store = $this->getValueStoreMock();
        $store->method('getAll')->willReturn(['value1', 'value2']);
        $column = new FileStorageColumn($this->getTmpDir(), $store, ['value1' => [1, 2], 'value2' => [3]]);

        $this->assertEquals(['value1', 'value2'], $column->getValues());
    }

    public function testGetSubset()
    {
        $column = new FileStorageColumn($this->getTmpDir(), $this->getValueStoreMock(), ['value1' => [1, 2], 'value2' => [3, 4], 'value3' => [5, 6]]);

        $this->assertEquals(['value1' => [1, 2], 'value3' => [5]], $column->getSubset([1, 2, 5, 7]));
    }

    public function testSave()
    {
        // TODO: fix docker settings
        $this->markTestSkipped();
    }

    protected function getValueStoreMock()
    {
        $store = $this->createMock(ValueStore::class);
        $store->method('getValueNo')->willReturnMap([
            ['value1', 0],
            ['value2', 1],
            ['value3', 2],
        ]);
        $store->method('get')->willReturnMap([
            [0, 'value1'],
            [1, 'value2'],
            [2, 'value3'],
        ]);

        return $store;
    }
}