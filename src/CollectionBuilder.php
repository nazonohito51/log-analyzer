<?php
declare(strict_types=1);

namespace LogAnalyzer;

use LogAnalyzer\Collection;
use LogAnalyzer\CollectionBuilder\IdSequence;
use LogAnalyzer\CollectionBuilder\Items\Item;
use LogAnalyzer\CollectionBuilder\LogFiles\LogFile;
use LogAnalyzer\CollectionBuilder\Parser\ApacheLogParser;
use LogAnalyzer\CollectionBuilder\Parser\LtsvParser;
use LogAnalyzer\CollectionBuilder\Parser\ParserInterface;
use LogAnalyzer\Collection\Column\ColumnFactory;
use LogAnalyzer\Collection\DatabaseInterface;
use LogAnalyzer\Collection\ColumnarDatabase;
use LogAnalyzer\Presenter\ProgressBarObserver;

class CollectionBuilder
{
    /**
     * @var LogFile[]
     */
    private $logFiles = [];
    private $database;
    private $progressBar;

    public function __construct(DatabaseInterface $database = null, ProgressBarObserver $progressBar = null)
    {
        $this->database = $database ?? CollectionBuilder::getDefaultDatabase();
        $this->progressBar = $progressBar ?? CollectionBuilder::getDefaultProgressBar();
    }

    protected static function getDefaultDatabase(): DatabaseInterface
    {
        return new ColumnarDatabase(new ColumnFactory());
    }

    protected static function getDefaultProgressBar(): ProgressBarObserver
    {
        return new ProgressBarObserver();
    }

    /**
     * @param string|array $files
     * @param ParserInterface $parser
     * @return $this
     */
    public function add($files, ParserInterface $parser): self
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
    public function addLtsv($files): self
    {
        $this->add($files, new LtsvParser());

        return $this;
    }

    /**
     * @param string|array $files
     * @param string $format kassner/log-parser format string. see https://github.com/kassner/log-parser
     * @return $this
     */
    public function addApacheLog($files, string $format = null): self
    {
        $this->add($files, new ApacheLogParser($format));

        return $this;
    }

    /**
     * @param bool $ignoreParseError
     * @return \LogAnalyzer\Collection
     */
    public function build($ignoreParseError = false): Collection
    {
        $idSequence = new IdSequence();
        $this->progressBar->start($this->getItemCount());

        $items = [];
        foreach ($this->logFiles as $logFile) {
            $logFile->ignoreParsedError($ignoreParseError);

            foreach ($logFile as $line) {
                $items[] = $idSequence->update()->now();
                foreach ($line as $key => $value) {
                    $this->database->addValue($idSequence->now(), $key, $value);
                }
                $this->progressBar->update(sprintf('Loading: %s(%d/%d) memory(KB): %d', $logFile->getFilename(), $logFile->key(), $logFile->count(), memory_get_usage() / 1024));
            }
        }

        $this->progressBar->end();
        $this->database->freeze();

        return new Collection($items, $this->database);
    }

    /**
     * @param array[] $data
     * @return \LogAnalyzer\Collection
     */
    public static function buildFromArray(array $data): Collection
    {
        $database = self::getDefaultDatabase();
        $idSequence = new IdSequence();

        $items = [];
        foreach ($data as $line) {
            foreach ($line as $key => $value) {
                $items[] = $idSequence->update()->now();
                $database->addValue($idSequence->now(), $key, $value);
            }
        }

        $database->freeze();

        return new Collection($items, $database);
    }

    /**
     * @return int
     */
    public function getItemCount(): int
    {
        $count = 0;
        foreach ($this->logFiles as $logFile) {
            $count += $logFile->count();
        }

        return $count;
    }
}
