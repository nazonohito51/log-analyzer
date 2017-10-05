<?php
namespace LogAnalyzer;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function getFixturePath($fixture_file)
    {
        $fixture_file = preg_match('<^/.*$>', $fixture_file) ?
            $fixture_file :
            '/' . $fixture_file;

        return __DIR__ . '/fixture' . $fixture_file;
    }
}
