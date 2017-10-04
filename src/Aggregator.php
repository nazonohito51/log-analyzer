<?php
namespace LogAnalyzer;

class Aggregator
{
    private $loaded = false;

    /**
     * @var LogFile[]
     */
    private $log_files = [];

    /**
     * @var Entry[]
     */
    private $entries = [];

    public function __construct(array $log_file_paths = [])
    {
        foreach ($log_file_paths as $path) {
            $this->log_files[] = new LogFile($path);
        }
    }

    public function addLogFile($log_file_path, array $options = [])
    {
        if ($this->loaded) {
            return false;
        }

        $this->log_files[] = new LogFile($log_file_path, $options);
        return true;
    }

    public function load()
    {
        foreach ($this->log_files as $log_file) {
            while ($entry = $log_file->getEntry()) {
                $this->entries[] = $entry;
            }
        }

        if (!empty($this->entries)) {
            $this->loaded = true;
        }
    }
}
