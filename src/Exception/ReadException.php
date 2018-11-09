<?php
declare(strict_types=1);

namespace LogAnalyzer\Exception;

class ReadException extends LogAnalyzerException
{
    public function __construct($line)
    {
        parent::__construct('error has occurred when read log files. line:' . $line);
    }
}
