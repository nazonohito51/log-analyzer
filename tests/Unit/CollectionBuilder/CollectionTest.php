<?php
namespace Tests\Unit\LogAnalyzer\CollectionBuilder;

use LogAnalyzer\CollectionBuilder\Collection;
use LogAnalyzer\CollectionBuilder\Items\Item;
use LogAnalyzer\CollectionBuilder\Items\ItemInterface;
use LogAnalyzer\CollectionBuilder\LogFiles\LogFile;
use LogAnalyzer\CollectionBuilder\Parser\LtsvParser;
use LogAnalyzer\Database\DatabaseInterface;
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

    public function testMap()
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
    }

    public function testMapByClosure()
    {
        $this->markTestSkipped();
        $database = $this->createMock(DatabaseInterface::class);
        $database->method('getValue')->willReturnMap([
            ['column', 1, 'value1'],
            ['column', 2, 'value1'],
            ['column', 3, 'value2']
        ]);
        $collection = new Collection([1, 2, 3], $database);

        $implode = $collection->columnValues('column', function ($value) {
            return strtoupper($value);
        });

        $this->assertEquals(['VALUE1', 'VALUE1', 'VALUE2'], $implode);
    }

    public function testExtract()
    {
        $this->markTestSkipped();
        $file = $this->getLogFileMock([
            "column:value1\tshould_extract_key:1",
            'column:value2',
            "column:value3\tshould_extract_key:1"
        ]);
        $collection = new Collection([
            new Item($file, 0),
            new Item($file, 1),
            new Item($file, 2),
        ]);

        $newCollection = $collection->filter(function (ItemInterface $item) {
            return $item->have('should_extract_key');
        });

        $this->assertEquals(2, $newCollection->count());
    }
}
