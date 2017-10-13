<?php
namespace LogAnalyzer\Entries;

use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    public function testAttributeAccess()
    {
        $entry = new Item([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $this->assertEquals('value1', $entry->get('key1'));
        $this->assertEquals('value2', $entry->get('key2'));
        $this->assertNull($entry->get('key3'));
    }
}
