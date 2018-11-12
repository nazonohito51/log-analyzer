<?php
declare(strict_types=1);

namespace Tests\Unit\LogAnalyzer\Database;

use LogAnalyzer\Collection\Column\ColumnFactory;
use LogAnalyzer\Collection\Column\ColumnInterface;
use LogAnalyzer\Collection\ColumnarDatabase;
use Tests\LogAnalyzer\TestCase;

class ColumnarDatabaseTest extends TestCase
{
    public function testAddColumn()
    {
        $stub = $this->createMock(ColumnInterface::class);
        $stub->expects($this->once())->method('add')->with(1, 'value1')->willReturnSelf();
        $stub->expects($this->once())->method('getItemIds')->with('value1')->willReturn([1]);
        $database = new ColumnarDatabase(new class ($stub) extends ColumnFactory {
            private $stub;
            public function __construct(ColumnInterface $stub)
            {
                $this->stub = $stub;
            }
            public function build($saveDir = ''): ColumnInterface
            {
                return $this->stub;
            }
        });

        $database->addValue(1, 'key1', 'value1');

        $this->assertEquals([1], $database->getItemIds('key1', 'value1'));
    }

    public function testGetItemIds()
    {
        $column = $this->createMock(ColumnInterface::class);
        $column->expects($this->once())->method('getItemIds')->with('value')->willReturn([1, 2, 3]);
        $factory = $this->createMock(ColumnFactory::class);
        $database = new ColumnarDatabase($factory, ['column' => $column]);

        $this->assertEquals([1, 2, 3], $database->getItemIds('column', 'value'));
    }

    public function testGetValue()
    {
        $column = $this->createMock(ColumnInterface::class);
        $column->expects($this->once())->method('getValue')->with(1)->willReturn('value');
        $factory = $this->createMock(ColumnFactory::class);
        $database = new ColumnarDatabase($factory, ['column' => $column]);

        $this->assertEquals('value', $database->getValue(1, 'column'));
    }

    public function testGetColumnValues()
    {
        $column = $this->createMock(ColumnInterface::class);
        $column->expects($this->once())->method('getValues')->willReturn(['value1', 'value2']);
        $factory = $this->createMock(ColumnFactory::class);
        $database = new ColumnarDatabase($factory, ['key1' => $column]);

        $this->assertEquals(['value1', 'value2'], $database->getValues('key1'));
    }

    public function testGetSubset()
    {
        $column = $this->createMock(ColumnInterface::class);
        $column->expects($this->once())->method('getSubset')->willReturn([
            'value1' => [1, 2],
            'value2' => [3]
        ]);
        $factory = $this->createMock(ColumnFactory::class);
        $database = new ColumnarDatabase($factory, ['column' => $column]);

        $this->assertEquals([
            'value1' => [1, 2],
            'value2' => [3]
        ], $database->getSubset([1, 2, 3], 'column'));
    }
}
