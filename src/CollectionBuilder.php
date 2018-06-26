<?php
namespace LogAnalyzer;

use LogAnalyzer\CollectionBuilder\Collection;
use LogAnalyzer\CollectionBuilder\LogFiles\LogFile;
use LogAnalyzer\CollectionBuilder\Parser\ApacheLogParser;
use LogAnalyzer\CollectionBuilder\Parser\LtsvParser;
use LogAnalyzer\CollectionBuilder\Parser\ParserInterface;

class CollectionBuilder
{
    /**
     * @var LogFile[]
     */
    private $log_files = [];

    /**
     * @param string|array $log_file_paths
     * @param ParserInterface $parser
     * @param array $options
     * @return $this
     */
    public function add($log_file_paths, ParserInterface $parser, array $options = [])
    {
        if (!is_array($log_file_paths)) {
            $log_file_paths = [$log_file_paths];
        }

        foreach ($log_file_paths as $log_file_path) {
            $this->log_files[] = new LogFile($log_file_path, $parser, $options);
        }

        return $this;
    }

    /**
     * @param string|array $log_file_paths
     * @param array $options
     * @return $this
     */
    public function addLtsv($log_file_paths, array $options = [])
    {
        $this->add($log_file_paths, new LtsvParser(), $options);

        return $this;
    }

    /**
     * @param string|array $log_file_paths
     * @param array $options
     * @param string $format
     * @return $this
     */
    public function addApacheLog($log_file_paths, array $options = [], $format = null)
    {
        $this->add($log_file_paths, new ApacheLogParser($format), $options);

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
