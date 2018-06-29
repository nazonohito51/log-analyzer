<?php
namespace Tests\Unit\LogAnalyzer;

use LogAnalyzer\CollectionBuilder;
use Tests\LogAnalyzer\Helpers\ItemMock;
use Tests\LogAnalyzer\TestCase;

class CollectionBuilderTest extends TestCase
{
    public function testCount()
    {
        $builder = new CollectionBuilder();
        $builder->addLtsv($this->getFixturePath('log.ltsv'));
        $aggregator = $builder->build();

        $this->assertEquals(9, $aggregator->count());
    }

    public function testItemInterface()
    {
        $builder = new CollectionBuilder(ItemMock::class);
        $builder->addLtsv($this->getFixturePath('log.ltsv'));
        $collection = $builder->build();

        $view = $collection->dimension('included_files', function (ItemMock $item) {
            return '[' . implode(',', $item->getIncludedFiles()) . ']';
        })->toArray();

        $this->assertCount(3, $view);
        $this->assertEquals('[bootstrap/autoload.php,public/index.php,app/Http/routes.php]', $view[0]['included_files']);
        $this->assertEquals(1, $view[0]['Count']);
        $this->assertEquals('[test.php]', $view[1]['included_files']);
        $this->assertEquals(7, $view[1]['Count']);
    }
}
