<?php
namespace LogAnalyzer\Database\Column;

use LogAnalyzer\Database\Column\FileStorageColumn;

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
