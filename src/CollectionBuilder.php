<?php
namespace LogAnalyzer;

use LogAnalyzer\CollectionBuilder\Collection;
use LogAnalyzer\CollectionBuilder\Items\Item;
use LogAnalyzer\CollectionBuilder\LogFiles\LogFile;
use LogAnalyzer\CollectionBuilder\Parser\ApacheLogParser;
use LogAnalyzer\CollectionBuilder\Parser\LtsvParser;
use LogAnalyzer\CollectionBuilder\Parser\ParserInterface;
use LogAnalyzer\Database\ColumnFactory;
use LogAnalyzer\Database\InMemoryDatabase;
use LogAnalyzer\Exception\InvalidArgumentException;
use LogAnalyzer\View\ProgressBar;

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
        $progressBar = new ProgressBar($this->getAllCount());

        $items = [];
        $itemId = 1;
        $database = new InMemoryDatabase(new ColumnFactory());
        foreach ($this->logFiles as $logFile) {
//            $logFile->ignoreParsedError($ignoreParseError);
//
//            foreach (range(0, $logFile->count()) as $linePos) {
//                /*
//                 * @var ItemInterface $item
//                 */
//                $item = new $this->itemClass($logFile, $linePos);
//                $items[] = $item;
//                $progressBar->update($logFile, $linePos);
//            }

            foreach ($logFile as $line) {
                $items[] = $itemId;
                $parsedLine = $logFile->getCurrentParsedLine();
                foreach ($parsedLine as $key => $value) {
                    $database->addColumnValue($key, $value, $itemId);
                }
                $itemId++;
            }
        }

        return new Collection($items, $database);
    }

    /**
     * @return int
     */
    public function getAllCount()
    {
        $count = 0;
        foreach ($this->logFiles as $logFile) {
            $count += $logFile->count();
        }

        return $count;
    }
}
