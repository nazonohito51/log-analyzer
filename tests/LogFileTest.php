<?php
namespace LogAnalyzer;

use LogAnalyzer\Items\Item;

class LogFileTest extends TestCase
{
    public function testApacheLog()
    {
        $log_file = new LogFile($this->getFixturePath('/apache.log'), [
            'format' => '%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"'
        ]);
        $items = $log_file->getItems();

        $this->assertEquals('133.130.35.34', $items[0]->get('host'));
        $this->assertEquals('23.96.184.214', $items[1]->get('host'));
        $this->assertEquals('93.158.152.5', $items[2]->get('host'));
        $this->assertEquals('93.158.152.5', $items[3]->get('host'));
        $this->assertEquals('133.130.35.34', $items[4]->get('host'));
        $this->assertEquals('133.130.35.35', $items[5]->get('host'));
        $this->assertEquals('66.249.79.82', $items[6]->get('host'));
        $this->assertEquals('66.249.79.82', $items[7]->get('host'));
    }

    public function testLtsvLog()
    {
        $log_file = new LogFile($this->getFixturePath('log.ltsv'), [
            'type' => 'ltsv'
        ]);
        $items = $log_file->getItems();

        $this->assertEquals('2016-10-12 15:31:18', $items[0]->get('date'));
        $this->assertEquals('2016-10-12 15:31:40', $items[1]->get('date'));
        $this->assertEquals('2016-10-12 15:32:09', $items[2]->get('date'));
        $this->assertEquals('2016-10-12 15:33:05', $items[3]->get('date'));
        $this->assertEquals('2016-10-12 15:33:40', $items[4]->get('date'));
        $this->assertEquals('2016-10-12 15:35:13', $items[5]->get('date'));
        $this->assertEquals('2016-10-12 15:35:40', $items[6]->get('date'));
        $this->assertEquals('2016-10-12 15:37:08', $items[7]->get('date'));
    }

    public function testItemClass()
    {
        $log_file = new LogFile($this->getFixturePath('log.ltsv'), [
            'type' => 'ltsv',
            'item' => ItemMock::class
        ]);
        $items = $log_file->getItems();

        $included_files = $items[0]->getIncludedFiles();
        $this->assertEquals('bootstrap/logging_included_files.php', $included_files[0]);
        $this->assertEquals('public/index.php', $included_files[1]);
        $this->assertEquals('public/conf/config.php', $included_files[2]);
    }
}

class ItemMock extends Item
{
    public function getIncludedFiles()
    {
        return explode(',', $this->get('included_files'));
    }
}
