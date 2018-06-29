<?php
namespace LogAnalyzer;

use LogAnalyzer\CollectionBuilder\Collection;
use LogAnalyzer\CollectionBuilder\Items\Item;
use LogAnalyzer\CollectionBuilder\LogFiles\LogFile;
use LogAnalyzer\CollectionBuilder\Parser\ApacheLogParser;
use LogAnalyzer\CollectionBuilder\Parser\LtsvParser;
use LogAnalyzer\CollectionBuilder\Parser\ParserInterface;
use LogAnalyzer\Exception\InvalidArgumentException;
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
        $progressBar = new Manager(0, $itemCount, 120);
        $progressBar->setFormat("%current%/%max% [%bar%] %percent%% %eta%   Loading: %file%(%line%/%lineMax%)");
        $progressBar->addReplacementRule('%file%', 5, function ($buffer, $registry) {
            /** @var Registry $registry */
            return $registry->getValue('file');
        });
        $progressBar->addReplacementRule('%line%', 5, function ($buffer, $registry) {
            /** @var Registry $registry */
            return $registry->getValue('line');
        });
        $progressBar->addReplacementRule('%lineMax%', 5, function ($buffer, $registry) {
            /** @var Registry $registry */
            return $registry->getValue('lineMax');
        });

        $items = [];
        foreach ($this->logFiles as $logFile) {
            $progressBar->getRegistry()->setValue('file', $logFile->getFilename());
            $progressBar->getRegistry()->setValue('line', 0);
            $progressBar->getRegistry()->setValue('lineMax', $logFile->getLineCount());
            foreach ($logFile as $linePos => $line) {
                $items[] = new $this->itemClass($logFile, $linePos);
                $progressBar->getRegistry()->setValue('line', $linePos);
                $progressBar->advance();
            }
        }

        return new Collection($items);
    }
}
