<?php
namespace LogAnalyzer;

class AggregatorTest extends TestCase
{
    public function testCount()
    {
        $aggregator = new Aggregator();
        $aggregator->addLogFile($this->getFixturePath('ltsv.log'), ['log_type' => 'ltsv']);
        $aggregator->load();

        $this->assertEquals(8, $aggregator->count());
    }
}
