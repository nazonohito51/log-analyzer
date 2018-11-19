<?php
declare(strict_types=1);

namespace LogAnalyzer\Collection\Column;

use LogAnalyzer\Collection\Column\FileStorageColumn\ValueStore;

class ColumnFactory
{
    public function build(string $saveDir = ''): ColumnInterface
    {
        if (empty($saveDir)) {
            $saveDir = $this->getDefaultSaveDir();
        }

        return new FileStorageColumn($this->getUniquePath($saveDir), new ValueStore());
    }

    public function load(string $path, string $saveDir = ''): ColumnInterface
    {
        if (empty($saveDir)) {
            $saveDir = $this->getDefaultSaveDir();
        }

        return FileStorageColumn::load($path, new ValueStore());
    }


    protected function getUniquePath($dir)
    {
        return tempnam($dir, 'log-analyzer-');
    }

    protected function getDefaultSaveDir()
    {
        return __DIR__ . '/../../../storage/';
    }
}
