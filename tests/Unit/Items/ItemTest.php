<?php
namespace Tests\Unit\LogAnalyzer\Items;

use LogAnalyzer\Items\Item;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    public function testAttributeAccess()
    {
        $item = new Item([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $this->assertEquals('value1', $item->get('key1'));
        $this->assertEquals('value2', $item->get('key2'));
        $this->assertNull($item->get('key3'));
    }
}
