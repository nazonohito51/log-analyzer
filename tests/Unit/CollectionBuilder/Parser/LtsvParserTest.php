<?php
declare(strict_types=1);

namespace Tests\Unit\LogAnalyzer\CollectionBuilder\Parser;

use LogAnalyzer\CollectionBuilder\Parser\LtsvParser;
use Tests\LogAnalyzer\TestCase;

class LtsvParserTest extends TestCase
{
    public function testParse()
    {
        $parser = new LtsvParser();

        $ret = $parser->parse('date:2016-10-12 15:31:18	SCRIPT_NAME:/index.php	REQUEST_URI:/	REQUEST_METHOD:GET	HTTP_USER_AGENT:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36	included_files:bootstrap/autoload.php,public/index.php,app/Http/routes.php');

        $this->assertEquals('2016-10-12 15:31:18', $ret['date']);
        $this->assertEquals('/index.php', $ret['SCRIPT_NAME']);
        $this->assertEquals('/', $ret['REQUEST_URI']);
        $this->assertEquals('GET', $ret['REQUEST_METHOD']);
        $this->assertEquals('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36', $ret['HTTP_USER_AGENT']);
        $this->assertEquals('bootstrap/autoload.php,public/index.php,app/Http/routes.php', $ret['included_files']);
    }
}
