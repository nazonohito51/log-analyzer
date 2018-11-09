<?php
declare(strict_types=1);

namespace LogAnalyzer\Database;

use LogAnalyzer\Database\Column\ColumnFactory;
use LogAnalyzer\Database\Column\ColumnInterface;

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

        $this->registerShutdownFunc();
    }

    public function registerShutdownFunc(): void
    {
        $column = $this->columns;
        register_shutdown_function(function () use ($column) {
            foreach ($this->columns as $column) {
                $column->delete();
            }
        });
    }

    public function addColumnValue($columnName, $value, $itemId): void
    {
        $this->isExistColumn($columnName) ?
            $this->getColumn($columnName)->add($value, $itemId) :
            $this->columns[$columnName] = $this->factory->build()->add($value, $itemId);
    }

    protected function isExistColumn($columnName): bool
    {
        return isset($this->columns[$columnName]);
    }

    protected function getColumn($columnName): ColumnInterface
    {
        return $this->columns[$columnName];
    }

    public function getItemIds($columnName, $value): array
    {
        if (!$this->isExistColumn($columnName)) {
            return [];
        }

        return $this->getColumn($columnName)->getItemIds($value);
    }

    public function getValue($columnName, $itemId)
    {
        return $this->isExistColumn($columnName) ? $this->getColumn($columnName)->getValue($itemId) : null;
    }

    public function getColumnValues($columnName): array
    {
        return $this->isExistColumn($columnName) ? $this->getColumn($columnName)->getValues() : null;
    }

    public function getColumnSubset($columnName, array $itemIds): array
    {
        return $this->isExistColumn($columnName) ? $this->getColumn($columnName)->getSubset($itemIds) : [];
    }

    public function save(): bool
    {
        foreach ($this->columns as $column) {
            if ($column->save() === false) {
                return false;
            }
        }

        return true;
    }
}
