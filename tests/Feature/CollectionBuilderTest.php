<?php
declare(strict_types=1);

namespace Tests\Feature;

use LogAnalyzer\CollectionBuilder;
use LogAnalyzer\Collection;
use LogAnalyzer\Collection\Column\ColumnFactory;
use LogAnalyzer\Collection\Column\ColumnInterface;
use LogAnalyzer\Collection\Column\InMemoryColumn;
use LogAnalyzer\Collection\ColumnarDatabase;
use LogAnalyzer\Presenter\ProgressBarObserver;
use Tests\LogAnalyzer\TestCase;

class CollectionBuilderTest extends TestCase
{
    public function testGetCollectionsRecursive()
    {
        $factory = new class extends ColumnFactory {
            public function build($saveDir = ''): ColumnInterface
            {
                return new InMemoryColumn();
            }
        };
        $builder = new CollectionBuilder(new ColumnarDatabase($factory), $this->createMock(ProgressBarObserver::class));
        $builder->addApacheLog($this->getFixturePath('apache.log'), '%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"');
        $collection = $builder->build();

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals(8, $collection->count());

        $view = $collection->dimension('status');
        $this->assertEquals([['status' => 200, '_count' => 7], ['status' => 302, '_count' => 1]], $view->toArray());

        $status200 = $view->getCollection(200);
        $status302 = $view->getCollection(302);

        $this->assertEquals(7, $status200->count());
        $this->assertEquals(1, $status302->count());

        $view = $status200->dimension('host');
        $this->assertEquals([
            ['host' => '133.130.35.34', '_count' => 2],
            ['host' => '93.158.152.5', '_count' => 3],
            ['host' => '133.130.35.35', '_count' => 1],
            ['host' => '66.249.79.82', '_count' => 1],
        ], $view->toArray());
    }
}