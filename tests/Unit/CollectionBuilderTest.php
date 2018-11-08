<?php
namespace Tests\Unit\LogAnalyzer;

use LogAnalyzer\CollectionBuilder;
use LogAnalyzer\Database\DatabaseInterface;
use Tests\LogAnalyzer\Helpers\ItemMock;
use Tests\LogAnalyzer\TestCase;

class CollectionBuilderTest extends TestCase
{
    public function testCount()
    {
        $builder = new CollectionBuilder($this->createMock(DatabaseInterface::class));
        $builder->addLtsv($this->getFixturePath('log.ltsv'));
        $collection = $builder->build();

        $this->assertEquals(8, $collection->count());
    }
}
