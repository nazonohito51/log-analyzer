<?php
namespace LogAnalyzer;

use LogAnalyzer\Aggregates\Collection;

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

    public function add($log_file_path, array $options = [])
    {
        $this->log_files[] = new LogFile($log_file_path, $options);

        return $this;
    }

    public function addLtsv($log_file_path, array $options = [])
    {
        $options['type'] = 'ltsv';
        $this->log_files[] = new LogFile($log_file_path, $options);

        return $this;
    }

    public function addApacheLog($log_file_path, array $options = [], $format = null)
    {
        $options['type'] = 'apache';
        $options['format'] = isset($format) ? $format : null;
        $this->log_files[] = new LogFile($log_file_path, $options);

        return $this;
    }

    public function build()
    {
        $entries = [];
        foreach ($this->log_files as $log_file) {
            while ($entry = $log_file->getEntry()) {
                $entries[] = $entry;
            }
        }

        return new Collection($entries);
    }
}
