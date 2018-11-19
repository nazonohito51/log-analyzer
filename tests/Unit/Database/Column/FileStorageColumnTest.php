<?php
declare(strict_types=1);

namespace Tests\Unit\LogAnalyzer\Database\Column;

use LogAnalyzer\Collection\Column\FileStorageColumn;
use LogAnalyzer\Collection\Column\FileStorageColumn\ValueStore;
use LogAnalyzer\Exception\RuntimeException;
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
        $column = new FileStorageColumn($this->getTmpFile(), $this->getValueStoreMock(), $initial);

        $column->add($addId, $addValue);

        $this->assertEquals($expected, $column->getItemIds($addValue));
    }

    public function testGetItems()
    {
        $column = new FileStorageColumn($this->getTmpFile(), $this->getValueStoreMock(), ['value1' => [1, 2, 3]]);

        $this->assertEquals([1, 2, 3], $column->getItemIds('value1'));
        $this->assertEquals([], $column->getItemIds('value2'));
    }

    public function testGetValue()
    {
        $column = new FileStorageColumn($this->getTmpFile(), $this->getValueStoreMock(), ['value1' => [1, 2], 'value2' => [3]]);

        $this->assertEquals('value1', $column->getValue(1));
        $this->assertEquals('value1', $column->getValue(2));
        $this->assertEquals('value2', $column->getValue(3));
        $this->assertNull($column->getValue(4));
    }

    public function testGetValues()
    {
        $store = $this->getValueStoreMock();
        $store->method('getAll')->willReturn(['value1', 'value2']);
        $column = new FileStorageColumn($this->getTmpFile(), $store, ['value1' => [1, 2], 'value2' => [3]]);

        $this->assertEquals(['value1', 'value2'], $column->getValues());
    }

    public function testGetSubset()
    {
        $column = new FileStorageColumn($this->getTmpFile(), $this->getValueStoreMock(), ['value1' => [1, 2], 'value2' => [3, 4], 'value3' => [5, 6]]);

        $this->assertEquals(['value1' => [1, 2], 'value3' => [5]], $column->getSubset([1, 2, 5, 7]));
    }

    public function testFreeze()
    {
        $store = $this->getValueStoreMock();
        $store->method('getAll')->willReturn(['value1', 'value2', 'value3']);
        $tmpFile = $this->getTmpFile();
        $column = new FileStorageColumn($tmpFile, $store, ['value1' => [1, 2], 'value2' => [3, 4], 'value3' => [5, 6]]);

        $column->freeze();

        $this->assertTrue(file_exists($tmpFile));
        $this->assertEquals([
            'value1' => [1, 2],
            'value2' => [3, 4],
            'value3' => [5, 6],
        ], unserialize(file_get_contents($tmpFile)));

        return $column;
    }

    /**
     * @param FileStorageColumn $column
     * @depends testFreeze
     */
    public function testAddAfterFreeze(FileStorageColumn $column)
    {
        $this->expectException(RuntimeException::class);

        $column->add(7, 'value4');
    }

    public function testSave()
    {
        $store = $this->getValueStoreMock();
        $store->method('getAll')->willReturn(['value1', 'value2', 'value3']);
        $path = $this->getTmpFile() . __FUNCTION__;
        $column = new FileStorageColumn($this->getTmpFile(), $store, ['value1' => [1, 2], 'value2' => [3, 4], 'value3' => [5, 6]]);

        $column->save($path);

        $file = new \SplFileObject($path);
        $savedContent = unserialize($file->fread($file->getSize()));
        $this->assertEquals([
            'value1' => [1, 2],
            'value2' => [3, 4],
            'value3' => [5, 6],
        ], $savedContent);

        return $savedContent;
    }

    public function testLoad()
    {
        $store = $this->getValueStoreMock();
        $store->method('getAll')->willReturn(['value1', 'value2', 'value3']);
        $path = $this->getTmpFile();
        $file = new \SplFileObject($path, 'w');
        $file->fwrite(serialize(['value1' => [1, 2], 'value2' => [3, 4], 'value3' => [5, 6]]));

        $column = FileStorageColumn::load($path, $store);

        $this->assertEquals(['value1', 'value2', 'value3'], $column->getValues());
        $this->assertEquals([1, 2], $column->getItemIds('value1'));
        $this->assertEquals([3, 4], $column->getItemIds('value2'));
        $this->assertEquals([5, 6], $column->getItemIds('value3'));
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
