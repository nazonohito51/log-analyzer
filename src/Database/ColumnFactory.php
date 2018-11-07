<?php
namespace LogAnalyzer\Database;

class ColumnFactory
{
    public function build($saveDir = '')
    {
        if (empty($saveDir)) {
            $saveDir = __DIR__ . '/../../storage/';
        }

        return new FileStorageColumn($saveDir);
    }
}
