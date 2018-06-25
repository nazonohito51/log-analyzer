<?php
namespace Tests\Unit\LogAnalyzer\Items;

use LogAnalyzer\CollectionBuilder\Collection;
use LogAnalyzer\CollectionBuilder\Items\Item;
use LogAnalyzer\CollectionBuilder\Items\ItemInterface;
use Tests\LogAnalyzer\TestCase;

class CollectionTest extends TestCase
{
    public function testDimension()
    {
        $collection = new Collection([
            new Item(['column' => 'value1']),
            new Item(['column' => 'value1']),
            new Item(['column' => 'value2']),
        ]);

        $view = $collection->dimension('column');
        $this->assertEquals(2, $view->count());
        $this->assertEquals(2, $view->getCollection('value1')->count());
        $this->assertEquals(1, $view->getCollection('value2')->count());
    }

    public function testDimensionByClosure()
    {
        $collection = new Collection([
            new Item(['column' => '<methodCall><methodName>getBlogInfo</methodName><params><param>111</param></params></methodCall>']),
            new Item(['column' => '<methodCall><methodName>getAdView</methodName><params><param>account</param></params></methodCall>']),
            new Item(['column' => '<methodCall><methodName>getBlogInfo</methodName><params><param>222</param></params></methodCall>']),
        ]);

        $view = $collection->dimension('methodName', function (ItemInterface $item) {
            if (($xml = simplexml_load_string($item->get('column'))) !== false) {
                return (string)$xml->methodName;
            }

            return null;
        });
        $this->assertEquals(2, $view->count());
        $this->assertEquals(2, $view->getCollection('getBlogInfo')->count());
        $this->assertEquals(1, $view->getCollection('getAdView')->count());
    }

    public function testImplode()
    {
        $collection = new Collection([
            new Item(['column' => 'value1']),
            new Item(['column' => 'value1']),
            new Item(['column' => 'value2']),
        ]);

        $implode = $collection->sum('column');

        $this->assertEquals(['value1', 'value1', 'value2'], $implode);
    }

    public function testImplodeByClosure()
    {
        $collection = new Collection([
            new Item(['column' => '1']),
            new Item(['column' => '2']),
            new Item(['column' => '3']),
        ]);

        $implode = $collection->sum(function ($carry, ItemInterface $item) {
            $carry += $item->get('column');
            return $carry;
        });

        $this->assertEquals(6, $implode);
    }

    public function testExtract()
    {
        $collection = new Collection([
            new Item(['column' => 'value1', 'should_extract_key' => 1]),
            new Item(['column' => 'value2']),
            new Item(['column' => 'value3', 'should_extract_key' => 1]),
        ]);

        $new_collection = $collection->filter(function (ItemInterface $item) {
            return $item->have('should_extract_key');
        });
        $this->assertEquals(2, $new_collection->count());
    }
}
