<?php
declare(strict_types=1);

namespace LogAnalyzer\Collection\Column;

use LogAnalyzer\Collection\Column\FileStorageColumn\ValueStore;
use LogAnalyzer\Exception\RuntimeException;

class FileStorageColumn implements ColumnInterface
{
    protected $path;
    protected $items = [];
    protected $values;
    protected $loaded = true;
    protected $frozen = false;

    public function __construct(string $path, ValueStore $valueStore, array $subset = [], bool $frozen = false)
    {
        $this->path = $path;
        $this->values = $valueStore;
        $this->frozen = $frozen;
        $this->loaded = !$frozen;
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
        if ($this->loaded === false) {
            $this->reload();
        }

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

        $this->saveToFile($this->path);
        $this->items = [];
        $this->values->reset();
        $this->loaded = false;

        return $this;
    }

    public function save(string $path): bool
    {
        return $this->saveToFile($path);
    }

    protected function saveToFile($path)
    {
        $file = new \SplFileObject($path, 'w');
        $data = $this->getSubset(array_keys($this->items));

        return $file->fwrite(serialize($data)) !== 0;
    }

    public static function load(string $path, ValueStore $store = null): ColumnInterface
    {
        return new self($path, $store ?? new ValueStore(), [], true);
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
            $this->loadFromSubset(unserialize(file_get_contents($this->path)));
            $this->loaded = true;
        }
    }

    public function delete(): bool
    {
        return unlink($this->path);
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
