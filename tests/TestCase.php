<?php
declare(strict_types=1);

namespace Tests\LogAnalyzer;

use LogAnalyzer\CollectionBuilder\LogFiles\LogFile;
use LogAnalyzer\CollectionBuilder\Parser\LtsvParser;
use phpDocumentor\Reflection\File;
use Tests\LogAnalyzer\Helpers\FileStreamWrapper;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var callable[]
     */
    protected $tearDownFuncs = [];

    public function tearDown()
    {
        parent::tearDown();

        $this->cleanupTmpDir();

        foreach ($this->tearDownFuncs as $tearDownFunc) {
            $tearDownFunc();
        }
        $this->tearDownFuncs = [];
    }

    protected function getFixturePath($fileName)
    {
        $fileName = preg_match('<^/.*$>', $fileName) ?
            $fileName :
            '/' . $fileName;

        return __DIR__ . '/Fixtures' . $fileName;
    }

    protected function getTmpDir()
    {
        return __DIR__ . '/tmp/';
    }

    protected function getLogFileMock(array $body)
    {
        return new LogFile($this->getFileMock($body), new LtsvParser());
    }

    protected function getFileMock(array $body)
    {
        FileStreamWrapper::enable(implode(PHP_EOL, $body));
        $this->tearDownFuncs[] = function () {
            FileStreamWrapper::disable();
        };

        return FileStreamWrapper::STREAM_PROTOCOL . '://wrapper.txt';
    }

    protected function cleanupTmpDir()
    {
        foreach (glob($this->getTmpDir() . '*') as $file) {
            if (!is_dir($file) && !preg_match('/\.gitkeep$/', $file)) {
                unlink($file);
            }
        }
    }
}
