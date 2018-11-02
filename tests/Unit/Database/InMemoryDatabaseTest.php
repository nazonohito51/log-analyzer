<?php
namespace Tests\Unit\LogAnalyzer\Database;

use LogAnalyzer\Database\ColumnFactory;
use LogAnalyzer\Database\ColumnInterface;
use LogAnalyzer\Database\InMemoryDatabase;
use Tests\LogAnalyzer\TestCase;

class InMemoryDatabaseTest extends TestCase
{
    public function testAddColumn()
    {
        $stub = $this->createMock(ColumnInterface::class);
        $database = new InMemoryDatabase(new class ($stub) extends ColumnFactory {
            private $stub;
            public function __construct(ColumnInterface $stub)
            {
                $this->stub = $stub;
            }
            public function build()
            {
                return $this->stub;
            }
        });

        $database->addColumn('key1', 'value1', 1);

        $this->assertEquals();
    }

    public function testGetColumn()
    {
        $database = new InMemoryDatabase();

        $column = $database->getColumn('key');

        $this->assertInstanceOf(ColumnInterface::class, $column);
    }
}
