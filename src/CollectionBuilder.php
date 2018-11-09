<?php
namespace LogAnalyzer;

use LogAnalyzer\CollectionBuilder\Collection;
use LogAnalyzer\CollectionBuilder\Items\Item;
use LogAnalyzer\CollectionBuilder\LogFiles\LogFile;
use LogAnalyzer\CollectionBuilder\Parser\ApacheLogParser;
use LogAnalyzer\CollectionBuilder\Parser\LtsvParser;
use LogAnalyzer\CollectionBuilder\Parser\ParserInterface;
use LogAnalyzer\Database\Column\ColumnFactory;
use LogAnalyzer\Database\DatabaseInterface;
use LogAnalyzer\Database\ColumnarDatabase;
use LogAnalyzer\View\ProgressBar;

class CollectionBuilder
{
    /**
     * @var LogFile[]
     */
    private $logFiles = [];
    /**
     * @var DatabaseInterface
     */
    private $database;

    /**
     * @param DatabaseInterface $database
     */
    public function __construct(DatabaseInterface $database = null)
    {
        $this->database = !is_null($database) ? $database : $this->getDefaultDatabase();
    }

    protected function getDefaultDatabase()
    {
        return new ColumnarDatabase(new ColumnFactory());
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
        $progressBar = new ProgressBar($this->getAllLogCount());

        $items = [];
        $itemId = 1;
        foreach ($this->logFiles as $logFile) {
            $logFile->ignoreParsedError($ignoreParseError);

            foreach ($logFile as $line) {
                if (is_null($line) || empty($line)) {
                    continue;
                }

                $items[] = $itemId;
                foreach ($line as $key => $value) {
                    $this->database->addColumnValue($key, $value, $itemId);
                }
                $itemId++;
                $progressBar->update($logFile);
            }
        }

        $this->database->save();

        return new Collection($items, $this->database);
    }

    /**
     * @return int
     */
    public function getAllLogCount()
    {
        $count = 0;
        foreach ($this->logFiles as $logFile) {
            $count += $logFile->count();
        }

        return $count;
    }
}
