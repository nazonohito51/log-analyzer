<?php
declare(strict_types=1);

namespace LogAnalyzer\Collection;

use LogAnalyzer\Collection\Column\ColumnFactory;
use LogAnalyzer\Collection\Column\ColumnInterface;

class ColumnarDatabase implements DatabaseInterface
{
    /**
     * @var ColumnInterface[]
     */
    protected $columns = [];
    /**
     * @var ColumnFactory
     */
    protected $factory;

    public function __construct(ColumnFactory $factory, array $columns = [])
    {
        $this->columns = $columns;
        $this->factory = $factory;
    }

    public function addValue($itemId, $columnName, $value): void
    {
        $this->haveColumn($columnName) ?
            $this->getColumn($columnName)->add($itemId, $value) :
            $this->columns[$columnName] = $this->factory->build()->add($itemId, $value);
    }

    protected function haveColumn($columnName): bool
    {
        return isset($this->columns[$columnName]);
    }

    protected function getColumn($columnName): ColumnInterface
    {
        return $this->columns[$columnName];
    }

    public function getItemIds($columnName, $value): array
    {
        if (!$this->haveColumn($columnName)) {
            return [];
        }

        return $this->getColumn($columnName)->getItemIds($value);
    }

    public function getValue($itemId, $columnName)
    {
        return $this->haveColumn($columnName) ? $this->getColumn($columnName)->getValue($itemId) : null;
    }

    public function getValues($columnName): array
    {
        return $this->haveColumn($columnName) ? $this->getColumn($columnName)->getValues() : null;
    }

    /**
     * group itemIds by column value.
     *
     * @param array $itemIds
     * @param $columnName
     * @return array
     */
    public function getSubset(array $itemIds, $columnName): array
    {
        return $this->haveColumn($columnName) ? $this->getColumn($columnName)->getSubset($itemIds) : [];
    }

    public function getScheme(): array
    {
        return array_keys($this->columns);
    }

    public function freeze(): bool
    {
        foreach ($this->columns as $column) {
            if ($column->freeze() === false) {
                return false;
            }
        }

        return true;
    }

    public function save(string $saveDir): bool
    {
        foreach ($this->columns as $columnName => $column) {
            $columnSavePath = $saveDir . '_database_' . $columnName;
            if ($column->save($columnSavePath) === false) {
                return false;
            }
        }

        return true;
    }

    public static function load(string $saveDir, ColumnFactory $factory): DatabaseInterface
    {
        $columns = [];
        foreach (glob($saveDir . '_database_*') as $file) {
            $columns[] = $factory->load($file);
        }

        return new self($factory, $columns);
    }
}
