<?php
declare(strict_types=1);

namespace LogAnalyzer\Collection\Column\FileStorageColumn;

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

    public function getAll(): array
    {
        return array_values($this->values);
    }

    public function getValueNo($value): int
    {
        $valueNo = array_search($value, $this->values);

        return $valueNo !== false ? $valueNo : $this->add($value);
    }

    protected function add($value): int
    {
        $this->values[] = $value;

        return count($this->values) - 1;
    }

    public function reset(): void
    {
        $this->values = [];
    }
}
