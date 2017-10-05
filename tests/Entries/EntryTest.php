<?php
namespace LogAnalyzer\Entries;

use PHPUnit\Framework\TestCase;

class EntryTest extends TestCase
{
    public function testAttributeAccess()
    {
        $entry = new Entry([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $this->assertEquals('value1', $entry->key1);
        $this->assertEquals('value2', $entry->key2);
        $this->assertNull($entry->key3);
    }
}
