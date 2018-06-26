<?php
namespace LogAnalyzer;

use LogAnalyzer\CollectionBuilder\Collection;
use LogAnalyzer\CollectionBuilder\Items\Item;
use LogAnalyzer\CollectionBuilder\Items\ItemInterface;
use LogAnalyzer\CollectionBuilder\LogFiles\LogFile;
use LogAnalyzer\CollectionBuilder\Parser\ApacheLogParser;
use LogAnalyzer\CollectionBuilder\Parser\LtsvParser;
use LogAnalyzer\CollectionBuilder\Parser\ParserInterface;
use LogAnalyzer\Exception\InvalidArgumentException;

class CollectionBuilder
{
    /**
     * @var LogFile[]
     */
    private $log_files = [];
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
     * @param string|array $log_file_paths
     * @param ParserInterface $parser
     * @return $this
     */
    public function add($log_file_paths, ParserInterface $parser)
    {
        if (!is_array($log_file_paths)) {
            $log_file_paths = [$log_file_paths];
        }

        foreach ($log_file_paths as $log_file_path) {
            $this->log_files[] = new LogFile($log_file_path, $parser, $this->itemClass);
        }

        return $this;
    }

    /**
     * @param string|array $log_file_paths
     * @return $this
     */
    public function addLtsv($log_file_paths)
    {
        $this->add($log_file_paths, new LtsvParser());

        return $this;
    }

    /**
     * @param string|array $log_file_paths
     * @param string $format
     * @return $this
     */
    public function addApacheLog($log_file_paths, $format = null)
    {
        $this->add($log_file_paths, new ApacheLogParser($format));

        return $this;
    }

    public function build()
    {
        $items = [];
        foreach ($this->log_files as $log_file) {
            while ($item = $log_file->getItem()) {
                $items[] = $item;
            }
        }

        return new Collection($items);
    }
}
