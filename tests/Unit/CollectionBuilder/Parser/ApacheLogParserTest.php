<?php
declare(strict_types=1);

namespace Tests\Unit\LogAnalyzer\CollectionBuilder\Parser;

use LogAnalyzer\CollectionBuilder\Parser\ApacheLogParser;
use Tests\LogAnalyzer\TestCase;

class ApacheLogParserTest extends TestCase
{
    public function testParse()
    {
        $parser = new ApacheLogParser('%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"');

        $ret = $parser->parse('133.130.35.34 - - [25/Jun/2017:04:02:05 +0900] "POST /users/1/articles HTTP/1.0" 200 346 "-" "Mozilla/5.0 (Windows CE) AppleWebKit/5350 (KHTML, like Gecko) Chrome/13.0.888.0 Safari/5350"');

        $this->assertEquals('133.130.35.34', $ret['host']);
        $this->assertEquals('-', $ret['user']);
        $this->assertEquals('25/Jun/2017:04:02:05 +0900', $ret['time']);
        $this->assertEquals('POST /users/1/articles HTTP/1.0', $ret['request']);
        $this->assertEquals('200', $ret['status']);
        $this->assertEquals('Mozilla/5.0 (Windows CE) AppleWebKit/5350 (KHTML, like Gecko) Chrome/13.0.888.0 Safari/5350', $ret['HeaderUserAgent']);
    }
}
