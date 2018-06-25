<?php
namespace LogAnalyzer;

use LogAnalyzer\CollectionBuilder\Collection;
use LogAnalyzer\CollectionBuilder\LogFiles\LogFile;

class CollectionBuilder
{
    /**
     * @var LogFile[]
     */
    private $log_files = [];

    public function __construct(array $log_file_paths = [])
    {
        foreach ($log_file_paths as $path) {
            $this->log_files[] = new LogFile($path);
        }
    }

    /**
     * @param string|array $log_file_paths
     * @param array $options
     * @return $this
     */
    public function add($log_file_paths, array $options = [])
    {
        if (!is_array($log_file_paths)) {
            $log_file_paths = [$log_file_paths];
        }

        foreach ($log_file_paths as $log_file_path) {
            $this->log_files[] = new LogFile($log_file_path, $options);
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
        $options['type'] = 'ltsv';
        $this->add($log_file_paths, $options);

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
        $options['type'] = 'apache';
        $options['format'] = isset($format) ? $format : null;
        $this->add($log_file_paths, $options);

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
