<?php
namespace Tests\Unit\LogAnalyzer\Database;

use LogAnalyzer\Database\Column\ColumnFactory;
use LogAnalyzer\Database\Column\ColumnInterface;
use LogAnalyzer\Database\ColumnarDatabase;
use Tests\LogAnalyzer\TestCase;

class ColumnarDatabaseTest extends TestCase
{
    public function testAddColumn()
    {
        $stub = $this->createMock(ColumnInterface::class);
        $stub->expects($this->once())->method('add')->with('value1', 1)->willReturnSelf();
        $stub->expects($this->once())->method('getItems')->with('value1')->willReturn([1]);
        $database = new ColumnarDatabase(new class ($stub) extends ColumnFactory {
            private $stub;
            public function __construct(ColumnInterface $stub)
            {
                $this->stub = $stub;
            }
            public function build($saveDir = '')
            {
                return $this->stub;
            }
        });

        $database->addColumnValue('key1', 'value1', 1);

        $this->assertEquals([1], $database->getItemIds('key1', 'value1'));
    }

    public function testGetItemIds()
    {
        $column = $this->createMock(ColumnInterface::class);
        $column->expects($this->once())->method('getItems')->with('value')->willReturn([1, 2, 3]);
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

        $this->assertEquals('value', $database->getValue('column', 1));
    }

    public function testGetColumnValues()
    {
        $column = $this->createMock(ColumnInterface::class);
        $column->expects($this->once())->method('getValues')->willReturn(['value1', 'value2']);
        $factory = $this->createMock(ColumnFactory::class);
        $database = new ColumnarDatabase($factory, ['key1' => $column]);

        $this->assertEquals(['value1', 'value2'], $database->getColumnValues('key1'));
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
        ], $database->getColumnSubset('column', [1, 2, 3]));
    }
}
