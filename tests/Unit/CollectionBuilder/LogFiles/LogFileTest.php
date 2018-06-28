<?php
namespace Tests\Unit\LogAnalyzer\CollectionBuilder\LogFiles;

use LogAnalyzer\CollectionBuilder\Items\Item;
use LogAnalyzer\CollectionBuilder\LogFiles\LogFile;
use LogAnalyzer\CollectionBuilder\Parser\ApacheLogParser;
use LogAnalyzer\CollectionBuilder\Parser\LtsvParser;
use Tests\LogAnalyzer\TestCase;

class LogFileTest extends TestCase
{
    public function testApacheLog()
    {
        $log_file = new LogFile(
            $this->getFixturePath('/apache.log'),
            new ApacheLogParser('%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"')
        );
        $items = [];
        foreach ($log_file as $linePos => $line) {
            $items[] = $line;
        }

        $this->assertEquals('133.130.35.34', $items[0]['host']);
        $this->assertEquals('23.96.184.214', $items[1]['host']);
        $this->assertEquals('93.158.152.5', $items[2]['host']);
        $this->assertEquals('93.158.152.5', $items[3]['host']);
        $this->assertEquals('133.130.35.34', $items[4]['host']);
        $this->assertEquals('133.130.35.35', $items[5]['host']);
        $this->assertEquals('93.158.152.5', $items[6]['host']);
        $this->assertEquals('66.249.79.82', $items[7]['host']);
    }

    public function testLtsvLog()
    {
        $log_file = new LogFile($this->getFixturePath('log.ltsv'), new LtsvParser());
        $items = [];
        foreach ($log_file as $linePos => $line) {
            $items[] = $line;
        }

        $this->assertEquals('2016-10-12 15:31:18', $items[0]['date']);
        $this->assertEquals('2016-10-12 15:31:40', $items[1]['date']);
        $this->assertEquals('2016-10-12 15:32:09', $items[2]['date']);
        $this->assertEquals('2016-10-12 15:33:05', $items[3]['date']);
        $this->assertEquals('2016-10-12 15:33:40', $items[4]['date']);
        $this->assertEquals('2016-10-12 15:35:13', $items[5]['date']);
        $this->assertEquals('2016-10-12 15:35:40', $items[6]['date']);
        $this->assertEquals('2016-10-12 15:37:08', $items[7]['date']);
    }

//    public function testItemClass()
//    {
//        $log_file = new LogFile($this->getFixturePath('log.ltsv'), new LtsvParser());
//        $items = [];
//        foreach ($log_file as $linePos => $line) {
//            $items[] = $line;
//        }
//
//        $included_files = $items[0]->getIncludedFiles();
//        $this->assertEquals('bootstrap/autoload.php', $included_files[0]);
//        $this->assertEquals('public/index.php', $included_files[1]);
//        $this->assertEquals('app/Http/routes.php', $included_files[2]);
//    }
}

class ItemMock extends Item
{
    public function getIncludedFiles()
    {
        return explode(',', $this->get('included_files'));
    }
}
