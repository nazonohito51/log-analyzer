<?php
namespace LogAnalyzer\Database\Column\FileStorageColumn;

class ValueStore
{
    protected $values = [];

    public function __construct(array $values = [])
    {
        foreach ($values as $value) {
            $this->values[] = $value;
        }
    }

    public function get($valueNo)
    {
        return isset($this->values[$valueNo]) ? $this->values[$valueNo] : null;
    }

    public function getAll()
    {
        return array_values($this->values);
    }

    public function getValueNo($value)
    {
        $valueNo = array_search($value, $this->values);

        return $valueNo !== false ? $valueNo : $this->add($value);
    }

    protected function add($value)
    {
        $this->values[] = $value;

        return count($this->values) - 1;
    }
}
