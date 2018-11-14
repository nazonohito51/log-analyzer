<?php
declare(strict_types=1);

namespace LogAnalyzer\Collection\Column;

use LogAnalyzer\Collection\Column\FileStorageColumn\ValueStore;
use LogAnalyzer\Exception\RuntimeException;

class FileStorageColumn implements ColumnInterface
{
    protected $file;
    protected $items = [];
    protected $values;
    protected $loaded = true;
    protected $frozen = false;

    public function __construct($saveDir, ValueStore $valueStore, array $subset = [])
    {
        if (!file_exists($saveDir)) {
            throw new \InvalidArgumentException();
        }

        $this->file = new \SplFileObject($this->getSavePath($saveDir), 'w+');
        $this->values = $valueStore;
        $this->loadFromSubset($subset);
    }

    protected function getSavePath($dir): string
    {
        $objectHash = spl_object_hash($this);
        return substr($dir, -1) === '/' ? $dir . $objectHash : $dir . '/' . $objectHash;
    }

    public function getItemIds($value): array
    {
        return array_keys($this->getItems(), $this->values->getValueNo($value));
    }

    public function getValue($itemId)
    {
        $valueNo = $this->getItems()[$itemId];
        return $this->values->get($valueNo);
    }

    public function getValues(): array
    {
        return $this->values->getAll();
    }

    public function getSubset(array $itemIds): array
    {
        $ret = [];
        $thisItemIds = $this->getItems();

        foreach ($itemIds as $itemId) {
            if (!isset($thisItemIds[$itemId])) {
                continue;
            }

            $valueNo = $thisItemIds[$itemId];
            $value = $this->values->get($valueNo);

            $ret[$value][] = $itemId;
        }

        return $ret;
    }

    public function add($itemId, $value): ColumnInterface
    {
        if ($this->frozen) {
            throw new RuntimeException('column is frozen');
        }

        $this->addData($value, $itemId);

        return $this;
    }

    public function freeze(): ColumnInterface
    {
        $this->frozen = true;

        $this->saveToFile($this->file);
        $this->items = [];
        $this->values->reset();
        $this->loaded = false;

        return $this;
    }

    public function save(string $path): bool
    {
        $file = new \SplFileObject($path, 'w+');

        return $this->saveToFile($file);
    }

    protected function saveToFile(\SplFileObject $file)
    {
        $data = $this->getSubset(array_keys($this->items));

        return $file->fwrite(serialize($data)) !== 0;
    }

    public static function load(string $path, string $saveDir = null, ValueStore $store = null): ColumnInterface
    {
        $file = new \SplFileObject($path);
        $savedContent = unserialize($file->fread($file->getSize()));

        return new self($saveDir, new ValueStore(), $savedContent);
    }

    protected function getItems(): array
    {
        if ($this->loaded === false) {
            $this->reload();
        }

        return $this->items;
    }

    protected function addData($value, $itemId): void
    {
        $this->items[$itemId] = $this->values->getValueNo($value);
    }

    protected function reload(): void
    {
        if ($this->loaded === false) {
            $this->file->rewind();
            $this->loadFromSubset(unserialize($this->file->fread($this->file->getSize())));
            $this->loaded = true;
        }
    }

    public function delete(): bool
    {
        return unlink($this->file->getRealPath());
    }

    protected function loadFromSubset(array $subset): void
    {
        foreach ($subset as $value => $itemIds) {
            foreach ($itemIds as $itemId) {
                $this->addData($value, $itemId);
            }
        }
    }
}
