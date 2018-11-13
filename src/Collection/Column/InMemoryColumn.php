<?php
declare(strict_types=1);

namespace LogAnalyzer\Collection\Column;

use LogAnalyzer\Exception\RuntimeException;

class InMemoryColumn implements ColumnInterface
{
    protected $data = [];
    protected $frozen = false;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function getItemIds($value): array
    {
        return isset($this->data[$value]) ? $this->data[$value] : [];
    }

    public function getValue($itemId)
    {
        foreach ($this->data as $value => $itemIds) {
            if (in_array($itemId, $itemIds)) {
                return $value;
            }
        }

        return null;
    }

    public function getValues(): array
    {
        return array_keys($this->data);
    }

    public function getSubset(array $itemIds): array
    {
        $ret = [];

        foreach ($itemIds as $itemId) {
            if (is_null($value = $this->getValue($itemId))) {
                continue;
            }

            $ret[$value][] = $itemId;
        }

        return $ret;
    }

    public function add($itemId, $value): ColumnInterface
    {
        if ($this->frozen) {
            throw new RuntimeException('column is frozen.');
        }

        isset($this->data[$value]) ? $this->data[$value][] = $itemId : $this->data[$value] = [$itemId];

        return $this;
    }

    public function freeze(): ColumnInterface
    {
        $this->frozen = true;

        return $this;
    }

    public function save(string $path): bool
    {
        $file = new \SplFileObject($path, 'w+');

        return $file->fwrite(serialize($this->data)) !== 0;
    }

    public function delete(): bool
    {
        return true;
    }
}
