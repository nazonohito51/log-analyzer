<?php
namespace LogAnalyzer\Exception;

class ReadException extends LogAnalyzerException
{
    public function __construct()
    {
        parent::__construct('error has occurred when read log files.');
    }
}
