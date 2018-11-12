<?php
declare(strict_types=1);

namespace Tests\Unit\LogAnalyzer\Database\Column;

use LogAnalyzer\Collection\Column\FileStorageColumn;
use LogAnalyzer\Collection\Column\FileStorageColumn\ValueStore;
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

        $this->assertEquals($expected, $column->getItemIds($addValue));
    }

    public function testGetItems()
    {
        $column = new FileStorageColumn($this->getTmpDir(), $this->getValueStoreMock(), ['value1' => [1, 2, 3]]);

        $this->assertEquals([1, 2, 3], $column->getItemIds('value1'));
        $this->assertEquals([], $column->getItemIds('value2'));
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
        $column = new FileStorageColumn($this->getTmpDir(), $this->getValueStoreMock(), ['value1' => [1, 2], 'value2' => [3, 4], 'value3' => [5, 6]]);

        $column->save();

        $file = new \SplFileObject($this->getTmpDir() . spl_object_hash($column));
        $line = unserialize($file->fread($file->getSize()));
        $this->assertEquals([
            1 => 0,
            2 => 0,
            3 => 1,
            4 => 1,
            5 => 2,
            6 => 2
        ], $line);

        return $column;
    }

    public function testDelete()
    {
        $column = new FileStorageColumn($this->getTmpDir(), $this->getValueStoreMock(), ['value1' => [1, 2], 'value2' => [3, 4], 'value3' => [5, 6]]);
        $column->save();

        $column->delete();

        $this->assertFalse(file_exists($this->getTmpDir() . spl_object_hash($column)));
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
