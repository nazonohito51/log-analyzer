<?php
declare(strict_types=1);

namespace Tests\Unit\LogAnalyzer;

use LogAnalyzer\CollectionBuilder;
use LogAnalyzer\Collection\DatabaseInterface;
use LogAnalyzer\Presenter\ProgressBarObserver;
use Tests\LogAnalyzer\Helpers\ItemMock;
use Tests\LogAnalyzer\TestCase;

class CollectionBuilderTest extends TestCase
{
    public function testCount()
    {
        $builder = new CollectionBuilder(
            $this->createMock(DatabaseInterface::class),
            $this->createMock(ProgressBarObserver::class)
        );
        $builder->addLtsv($this->getFixturePath('log.ltsv'));
        $collection = $builder->build();

        $this->assertEquals(8, $collection->count());
    }

    public function testBuildFromArray()
    {
        $collection = CollectionBuilder::buildFromArray([
            ['key1' => 'value1', 'key2' => 'value2'],
            ['key1' => 'value3', 'key2' => 'value4'],
            ['key1' => 'value5', 'key2' => 'value6'],
        ]);

        $this->assertSame(3, $collection->count());
    }
}
