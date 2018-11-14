<?php
declare(strict_types=1);

namespace LogAnalyzer\Collection\Column;

use LogAnalyzer\Collection\Column\FileStorageColumn\ValueStore;

class ColumnFactory
{
    public function build($saveDir = ''): ColumnInterface
    {
        if (empty($saveDir)) {
            $saveDir = $this->getDefaultSaveDir();
        }

        return new FileStorageColumn($saveDir, new ValueStore());
    }

    public function load($path, $saveDir = ''): ColumnInterface
    {
        if (empty($saveDir)) {
            $saveDir = $this->getDefaultSaveDir();
        }

        return FileStorageColumn::load($path, $saveDir, new ValueStore());
    }

    protected function getDefaultSaveDir()
    {
        return __DIR__ . '/../../../storage/';
    }
}
