<?php
namespace LogAnalyzer;

class AggregatorTest extends TestCase
{
    public function testCount()
    {
        $builder = new AggregatorBuilder();
        $builder->add($this->getFixturePath('ltsv.log'), ['log_type' => 'ltsv']);
        $aggregator = $builder->build();

        $this->assertEquals(8, $aggregator->count());
    }
}
