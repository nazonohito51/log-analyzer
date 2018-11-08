<?php
namespace LogAnalyzer\Database;

class InMemoryColumn implements ColumnInterface
{
    protected $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function add($value, $itemId)
    {
        isset($this->data[$value]) ? $this->data[$value][] = $itemId : $this->data[$value] = [$itemId];

        return $this;
    }

    public function getItems($value)
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

    public function getValues()
    {
        return array_keys($this->data);
    }

    public function getSubset(array $itemIds)
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

    public function save()
    {
        return true;
    }
}
