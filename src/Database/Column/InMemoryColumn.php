<?php
declare(strict_types=1);

namespace LogAnalyzer\Database\Column;

class InMemoryColumn implements ColumnInterface
{
    protected $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function add($value, $itemId): ColumnInterface
    {
        isset($this->data[$value]) ? $this->data[$value][] = $itemId : $this->data[$value] = [$itemId];

        return $this;
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

    public function save(): bool
    {
        return true;
    }

    public function delete(): bool
    {
        return true;
    }
}
