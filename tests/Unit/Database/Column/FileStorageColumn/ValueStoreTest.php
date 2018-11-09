<?php
declare(strict_types=1);

namespace Tests\Unit\LogAnalyzer\Database\Column\FileStorageColumn;

use LogAnalyzer\Database\Column\FileStorageColumn\ValueStore;
use Tests\LogAnalyzer\TestCase;

class ValueStoreTest extends TestCase
{
    public function testGet()
    {
        $store = new ValueStore(['value1', 'value2', 'value3']);

        $this->assertEquals('value1', $store->get(0));
        $this->assertEquals('value2', $store->get(1));
        $this->assertEquals('value3', $store->get(2));
        $this->assertNull($store->get(3));
    }

    public function testGetAll()
    {
        $store = new ValueStore(['value1', 'value2', 'value3']);

        $this->assertEquals(['value1', 'value2', 'value3'], $store->getAll());
    }

    public function testGetValueNo()
    {
        $store = new ValueStore(['value1', 'value2', 'value3']);

        $this->assertEquals(0, $store->getValueNo('value1'));
        $this->assertEquals(1, $store->getValueNo('value2'));
        $this->assertEquals(2, $store->getValueNo('value3'));
        $this->assertEquals(3, $store->getValueNo('value4'));
    }
}
