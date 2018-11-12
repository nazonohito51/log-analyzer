<?php
declare(strict_types=1);

namespace Tests\Unit\LogAnalyzer;

use LogAnalyzer\Collection;
use LogAnalyzer\CollectionBuilder\Items\Item;
use LogAnalyzer\CollectionBuilder\Items\ItemInterface;
use LogAnalyzer\CollectionBuilder\LogFiles\LogFile;
use LogAnalyzer\CollectionBuilder\Parser\LtsvParser;
use LogAnalyzer\Collection\DatabaseInterface;
use Tests\LogAnalyzer\TestCase;

class CollectionTest extends TestCase
{
    public function testDimension()
    {
        $database = $this->createMock(DatabaseInterface::class);
        $database->method('getColumnSubset')->with('key1', [1, 2, 3])->willReturn([
            'value1' => [1, 2],
            'value2' => [3]
        ]);
        $collection = new Collection([1, 2, 3], $database);

        $view = $collection->dimension('key1');

        $this->assertEquals(2, $view->count());
        $this->assertEquals(2, $view->getCollection('value1')->count());
        $this->assertEquals(1, $view->getCollection('value2')->count());
    }

    public function testDimensionByClosure()
    {
        $database = $this->createMock(DatabaseInterface::class);
        $database->method('getColumnSubset')->with('key1', [1, 2, 3])->willReturn([
            '<methodCall><methodName>getBlogInfo</methodName><params><param>111</param></params></methodCall>' => [1],
            '<methodCall><methodName>getAdView</methodName><params><param>account</param></params></methodCall>' => [2],
            '<methodCall><methodName>getBlogInfo</methodName><params><param>222</param></params></methodCall>' => [3]
        ]);
        $collection = new Collection([1, 2, 3], $database);

        $view = $collection->dimension('key1', function ($value) {
            if (($xml = simplexml_load_string($value)) !== false) {
                return (string)$xml->methodName;
            }

            return null;
        });

        $this->assertEquals(2, $view->count());
        $this->assertEquals(2, $view->getCollection('getBlogInfo')->count());
        $this->assertEquals(1, $view->getCollection('getAdView')->count());
    }

    public function testColumnValues()
    {
        $database = $this->createMock(DatabaseInterface::class);
        $database->method('getValue')->willReturnMap([
            ['column', 1, 'value1'],
            ['column', 2, 'value1'],
            ['column', 3, 'value2']
        ]);
        $collection = new Collection([1, 2, 3], $database);

        $implode = $collection->columnValues('column');

        $this->assertEquals(['value1', 'value1', 'value2'], $implode);

        return $collection;
    }

    /**
     * @param Collection $collection
     * @return Collection
     * @depends testColumnValues
     */
    public function testCacheColumnValues(Collection $collection)
    {
        $collection->cacheColumnValues('column', ['cachedValue1', 'cachedValue2']);

        $this->assertEquals(['cachedValue1', 'cachedValue2'], $collection->columnValues('column'));

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testCacheColumnValues
     */
    public function testFlushCache(Collection $collection)
    {
        $collection->flushCache();

        $this->assertEquals(['value1', 'value1', 'value2'], $collection->columnValues('column'));
    }

    public function testFilter()
    {
        $database = $this->createMock(DatabaseInterface::class);
        $database->method('getValue')->willReturnMap([
            ['column', 1, 100],
            ['column', 2, 101],
            ['column', 3, 102]
        ]);
        $collection = new Collection([1, 2, 3], $database);

        $newCollection = $collection->filter('column', function ($value) {
            return $value >= 101;
        });

        $this->assertEquals(2, $newCollection->count());
    }
}
