<?php
namespace LogAnalyzer;

use LogAnalyzer\CollectionBuilder\Collection;
use LogAnalyzer\CollectionBuilder\Items\Item;
use LogAnalyzer\CollectionBuilder\LogFiles\LogFile;
use LogAnalyzer\CollectionBuilder\Parser\ApacheLogParser;
use LogAnalyzer\CollectionBuilder\Parser\LtsvParser;
use LogAnalyzer\CollectionBuilder\Parser\ParserInterface;
use LogAnalyzer\Exception\InvalidArgumentException;
use LogAnalyzer\View\ProgressBar;
use ProgressBar\Manager;
use ProgressBar\Registry;

class CollectionBuilder
{
    /**
     * @var LogFile[]
     */
    private $logFiles = [];
    private $itemClass;

    /**
     * @param string|null $itemClass
     */
    public function __construct($itemClass = null)
    {
        if (!is_null($itemClass) && !class_exists($itemClass)) {
            throw new InvalidArgumentException('item class is not found.');
        }

        $this->itemClass = !is_null($itemClass) ? $itemClass : $this->getDefaultItemClass();
    }

    protected function getDefaultItemClass()
    {
        return Item::class;
    }

    /**
     * @param string|array $files
     * @param ParserInterface $parser
     * @return $this
     */
    public function add($files, ParserInterface $parser)
    {
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            $this->logFiles[] = new LogFile($file, $parser);
        }

        return $this;
    }

    /**
     * @param string|array $files
     * @return $this
     */
    public function addLtsv($files)
    {
        $this->add($files, new LtsvParser());

        return $this;
    }

    /**
     * @param string|array $files
     * @param string $format kassner/log-parser format string. see https://github.com/kassner/log-parser
     * @return $this
     */
    public function addApacheLog($files, $format = null)
    {
        $this->add($files, new ApacheLogParser($format));

        return $this;
    }

    public function build($ignoreParseError = false)
    {
        $itemCount = 0;
        foreach ($this->logFiles as $logFile) {
            $logFile->ignoreParsedError($ignoreParseError);
            $itemCount += $logFile->getLineCount();
        }

        $progressBar = new ProgressBar($itemCount);

        $items = [];
        foreach ($this->logFiles as $logFile) {
            foreach (range(0, $logFile->getLineCount()) as $linePos) {
                $items[] = new $this->itemClass($logFile, $linePos);
                $progressBar->update($logFile, $linePos);
            }
        }

        return new Collection($items);
    }
}
