<?php
namespace Tests\Unit\LogAnalyzer;

use LogAnalyzer\CollectionBuilder;
use Tests\LogAnalyzer\TestCase;

class CollectionBuilderTest extends TestCase
{
    public function testCount()
    {
        $builder = new CollectionBuilder();
        $builder->addLtsv($this->getFixturePath('log.ltsv'));
        $aggregator = $builder->build();

        $this->assertEquals(8, $aggregator->count());
    }
}
