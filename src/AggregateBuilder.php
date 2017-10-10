<?php
namespace LogAnalyzer;

use LogAnalyzer\Aggregates\EntryAggregate;

class AggregateBuilder
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

    public function build()
    {
        $entries = [];
        foreach ($this->log_files as $log_file) {
            while ($entry = $log_file->getEntry()) {
                $entries[] = $entry;
            }
        }

        return new EntryAggregate($entries);
    }
}
