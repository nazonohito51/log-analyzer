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
        $database->method('getColumnValues')->willReturn(['value1', 'value2']);
        $database->method('getItemIds')->willReturnMap([
            ['key1', 'value1', [1, 2]],
            ['key1', 'value2', [3]]
        ]);
        $collection = new Collection([1, 2, 3], $database);

        $view = $collection->dimension('key1');

        $this->assertEquals(2, $view->count());
        $this->assertEquals(2, $view->getCollection('value1')->count());
        $this->assertEquals(1, $view->getCollection('value2')->count());
    }

    public function testDimensionByClosure()
    {
        $this->markTestSkipped();
        $file = $this->getLogFileMock([
            'column:<methodCall><methodName>getBlogInfo</methodName><params><param>111</param></params></methodCall>',
            'column:<methodCall><methodName>getAdView</methodName><params><param>account</param></params></methodCall>',
            'column:<methodCall><methodName>getBlogInfo</methodName><params><param>222</param></params></methodCall>'
        ]);
        $collection = new Collection([
            new Item($file, 0),
            new Item($file, 1),
            new Item($file, 2),
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

    public function testSum()
    {
        $database = $this->createMock(DatabaseInterface::class);
        $database->method('getValue')->willReturnMap([
            ['column', 1, 'value1'],
            ['column', 2, 'value1'],
            ['column', 3, 'value2']
        ]);
        $collection = new Collection([1, 2, 3], $database);

        $implode = $collection->sum('column');

        $this->assertEquals(['value1', 'value1', 'value2'], $implode);
    }

    public function testSumByClosure()
    {
        $this->markTestSkipped();
        $file = $this->getLogFileMock([
            'column:1',
            'column:2',
            'column:3'
        ]);
        $collection = new Collection([
            new Item($file, 0),
            new Item($file, 1),
            new Item($file, 2),
        ]);

        $implode = $collection->sum(function ($carry, ItemInterface $item) {
            $carry += $item->get('column');
            return $carry;
        });

        $this->assertEquals(6, $implode);
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
