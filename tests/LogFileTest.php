<?php
namespace LogAnalyzer;

class LogFileTest extends TestCase
{
    public function testApacheLog()
    {
        $log_file = new LogFile($this->getFixturePath('/apache.log'), [
            'format' => '%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"'
        ]);
        $entries = $log_file->getEntries();

        $this->assertEquals('133.130.35.34', $entries[0]->get('host'));
        $this->assertEquals('23.96.184.214', $entries[1]->get('host'));
        $this->assertEquals('93.158.152.5', $entries[2]->get('host'));
        $this->assertEquals('93.158.152.5', $entries[3]->get('host'));
        $this->assertEquals('133.130.35.34', $entries[4]->get('host'));
        $this->assertEquals('133.130.35.35', $entries[5]->get('host'));
        $this->assertEquals('66.249.79.82', $entries[6]->get('host'));
        $this->assertEquals('66.249.79.82', $entries[7]->get('host'));
    }

    public function testLtsvLog()
    {
        $log_file = new LogFile($this->getFixturePath('log.ltsv'), [
            'type' => 'ltsv'
        ]);
        $entries = $log_file->getEntries();

        $this->assertEquals('2016-10-12 15:31:18', $entries[0]->get('date'));
        $this->assertEquals('2016-10-12 15:31:40', $entries[1]->get('date'));
        $this->assertEquals('2016-10-12 15:32:09', $entries[2]->get('date'));
        $this->assertEquals('2016-10-12 15:33:05', $entries[3]->get('date'));
        $this->assertEquals('2016-10-12 15:33:40', $entries[4]->get('date'));
        $this->assertEquals('2016-10-12 15:35:13', $entries[5]->get('date'));
        $this->assertEquals('2016-10-12 15:35:40', $entries[6]->get('date'));
        $this->assertEquals('2016-10-12 15:37:08', $entries[7]->get('date'));
    }
}
