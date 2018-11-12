<?php
declare(strict_types=1);

namespace Tests\Unit\LogAnalyzer;

use LogAnalyzer\Collection;
use LogAnalyzer\CollectionBuilder\Items\Item;
use LogAnalyzer\CollectionBuilder\Items\ItemInterface;
use LogAnalyzer\CollectionBuilder\LogFiles\LogFile;
use LogAnalyzer\CollectionBuilder\Parser\LtsvParser;
use LogAnalyzer\Collection\DatabaseInterface;
use LogAnalyzer\View;
use Tests\LogAnalyzer\TestCase;

class CollectionTest extends TestCase
{
    public function testDimension()
    {
        $database = $this->createMock(DatabaseInterface::class);
        $database->method('getColumnSubset')->with('column1', [1, 2, 3])->willReturn([
            'value1' => [1, 2],
            'value2' => [3]
        ]);
        $collection = new Collection([1, 2, 3], $database);

        $view = $collection->dimension('column1');

        $this->assertInstanceOf(View::class, $view);
        $this->assertEquals(2, $view->count());
        $this->assertEquals(2, $view->getCollection('value1')->count());
        $this->assertEquals(1, $view->getCollection('value2')->count());
    }

    public function testDimensionByClosure()
    {
        $database = $this->createMock(DatabaseInterface::class);
        $database->method('getColumnSubset')->with('column1', [1, 2, 3])->willReturn([
            '<methodCall><methodName>getBlogInfo</methodName><params><param>111</param></params></methodCall>' => [1],
            '<methodCall><methodName>getAdView</methodName><params><param>account</param></params></methodCall>' => [2],
            '<methodCall><methodName>getBlogInfo</methodName><params><param>222</param></params></methodCall>' => [3]
        ]);
        $collection = new Collection([1, 2, 3], $database);

        $view = $collection->dimension('column1', function ($value) {
            if (($xml = simplexml_load_string($value)) !== false) {
                return (string)$xml->methodName;
            }

            return null;
        });

        $this->assertInstanceOf(View::class, $view);
        $this->assertEquals(2, $view->count());
        $this->assertEquals(2, $view->getCollection('getBlogInfo')->count());
        $this->assertEquals(1, $view->getCollection('getAdView')->count());
    }

    public function testGroupBy()
    {
        $database = $this->createMock(DatabaseInterface::class);
        $database->method('getColumnSubset')->with('column1', [1, 2, 3])->willReturn([
            'value1' => [1, 2],
            'value2' => [3]
        ]);
        $collection = new Collection([1, 2, 3], $database);

        $collections = $collection->groupBy('column1');

        $this->assertEquals(2, count($collections));
        $this->assertInstanceOf(Collection::class, $collections['value1']);
        $this->assertEquals(2, $collections['value1']->count());
        $this->assertInstanceOf(Collection::class, $collections['value2']);
        $this->assertEquals(1, $collections['value2']->count());
    }

    public function testGroupByClosure()
    {
        $database = $this->createMock(DatabaseInterface::class);
        $database->method('getColumnSubset')->with('column1', [1, 2, 3])->willReturn([
            '<methodCall><methodName>getBlogInfo</methodName><params><param>111</param></params></methodCall>' => [1],
            '<methodCall><methodName>getAdView</methodName><params><param>account</param></params></methodCall>' => [2],
            '<methodCall><methodName>getBlogInfo</methodName><params><param>222</param></params></methodCall>' => [3]
        ]);
        $collection = new Collection([1, 2, 3], $database);

        $collections = $collection->groupBy('column1', function ($value) {
            if (($xml = simplexml_load_string($value)) !== false) {
                return (string)$xml->methodName;
            }

            return null;
        });

        $this->assertEquals(2, count($collections));
        $this->assertInstanceOf(Collection::class, $collections['getBlogInfo']);
        $this->assertEquals(2, $collections['getBlogInfo']->count());
        $this->assertInstanceOf(Collection::class, $collections['getAdView']);
        $this->assertEquals(1, $collections['getAdView']->count());
    }

    public function testValues()
    {
        $database = $this->createMock(DatabaseInterface::class);
        $database->method('getValue')->willReturnMap([
            ['column', 1, 'value1'],
            ['column', 2, 'value1'],
            ['column', 3, 'value2']
        ]);
        $collection = new Collection([1, 2, 3], $database);

        $implode = $collection->values('column');

        $this->assertEquals(['value1', 'value1', 'value2'], $implode);

        return $collection;
    }

    /**
     * @param Collection $collection
     * @return Collection
     * @depends testValues
     */
    public function testCache(Collection $collection)
    {
        $collection->cache('column', ['cachedValue1', 'cachedValue2']);

        $this->assertEquals(['cachedValue1', 'cachedValue2'], $collection->values('column'));

        return $collection;
    }

    /**
     * @param Collection $collection
     * @depends testCache
     */
    public function testFlush(Collection $collection)
    {
        $collection->flush();

        $this->assertEquals(['value1', 'value1', 'value2'], $collection->values('column'));
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
