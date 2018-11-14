<?php
declare(strict_types=1);

namespace Tests\Unit\LogAnalyzer\Database\Column;

use LogAnalyzer\Collection\Column\ColumnInterface;
use LogAnalyzer\Collection\Column\InMemoryColumn;
use LogAnalyzer\Exception\RuntimeException;
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

    public function testFreeze()
    {
        $this->expectException(RuntimeException::class);

        $column = new InMemoryColumn();
        $column->freeze();

        $column->add(1, 'value');
    }

    public function testSave()
    {
        $column = new InMemoryColumn(['value1' => [1, 2], 'value2' => [3]]);
        $path = $this->getTmpDir() . __FUNCTION__;

        $ret = $column->save($path);
        $file = new \SplFileObject($path);
        $content = unserialize($file->fread($file->getSize()));

        $this->assertTrue($ret);
        $this->assertEquals(['value1' => [1, 2], 'value2' => [3]], $content);

        return $content;
    }

    /**
     * @param $content
     * @return ColumnInterface
     * @depends testSave
     */
    public function testLoad($content)
    {
        $path = $this->getTmpDir() . __FUNCTION__;
        $file = new \SplFileObject($path, 'w');
        $file->fwrite(serialize($content));

        $column = InMemoryColumn::load($path);

        $this->assertEquals(['value1', 'value2'], $column->getValues());

        return $column;
    }

    /**
     * @param InMemoryColumn $column
     * @depends testLoad
     */
    public function testAddAfterLoad(InMemoryColumn $column)
    {
        $this->expectException(RuntimeException::class);

        $column->add(4, 'value3');
    }
}
