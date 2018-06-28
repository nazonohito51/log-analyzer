<?php
namespace Tests\Unit\LogAnalyzer\CollectionBuilder\Items;

use LogAnalyzer\CollectionBuilder\Items\Item;
use Tests\LogAnalyzer\TestCase;

class ItemTest extends TestCase
{
    public function testAttributeAccess()
    {
        $file = $this->getLogFileMock([
            "key1:value11\tkey2:value12",
            "key1:value21\tkey2:value22",
            "key1:value31\tkey2:value32",
            "key1:value41\tkey2:value42",
            "key1:value51\tkey2:value52",
        ]);

        $item = new Item($file, 2);

        $this->assertEquals('value31', $item->get('key1'));
        $this->assertEquals('value32', $item->get('key2'));
        $this->assertNull($item->get('key3'));
    }
}
