<?php
namespace Tests\Feature;

use LogAnalyzer\CollectionBuilder;
use LogAnalyzer\CollectionBuilder\Collection;
use Tests\LogAnalyzer\TestCase;

class CollectionBuilderTest extends TestCase
{
    public function testGetCollectionsRecursive()
    {
        $builder = new CollectionBuilder();
        $builder->addApacheLog($this->getFixturePath('apache.log'), '%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"');
        $collection = $builder->build();

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals(8, $collection->count());

        $view = $collection->dimension('status');
        $this->assertEquals([['status' => 200, 'Count' => 7], ['status' => 302, 'Count' => 1]], $view->toArray());

        $status200 = $view->getCollection('200');
        $status302 = $view->getCollection('302');

        $this->assertEquals(7, $status200->count());
        $this->assertEquals(1, $status302->count());

        $view = $status200->dimension('host');
        $this->assertEquals([
            ['host' => '133.130.35.34', 'Count' => 2],
            ['host' => '93.158.152.5', 'Count' => 3],
            ['host' => '133.130.35.35', 'Count' => 1],
            ['host' => '66.249.79.82', 'Count' => 1],
        ], $view->toArray());
    }
}