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

        $this->registerShutdownFunc();
    }

    protected function registerShutdownFunc(): void
    {
        $column = $this->columns;
        register_shutdown_function(function () use ($column) {
            foreach ($this->columns as $column) {
                $column->delete();
            }
        });
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
