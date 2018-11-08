<?php
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
    }

    public function addColumnValue($columnName, $value, $itemId)
    {
        $this->isExistColumn($columnName) ?
            $this->getColumn($columnName)->add($value, $itemId) :
            $this->columns[$columnName] = $this->factory->build()->add($value, $itemId);
    }

    protected function isExistColumn($columnName)
    {
        return isset($this->columns[$columnName]);
    }

    protected function getColumn($columnName)
    {
        return $this->columns[$columnName];
    }

    public function getItemIds($columnName, $value)
    {
        if (!$this->isExistColumn($columnName)) {
            return [];
        }

        return $this->getColumn($columnName)->getItems($value);
    }

    public function getValue($columnName, $itemId)
    {
        return $this->isExistColumn($columnName) ? $this->getColumn($columnName)->getValue($itemId) : null;
    }

    public function getColumnValues($columnName)
    {
        return $this->isExistColumn($columnName) ? $this->getColumn($columnName)->getValues() : null;
    }

    public function getColumnSubset($columnName, array $itemIds)
    {
        return $this->isExistColumn($columnName) ? $this->getColumn($columnName)->getSubset($itemIds) : [];
    }

    public function save()
    {
        foreach ($this->columns as $column) {
            if ($column->save() === false) {
                return false;
            }
        }

        return true;
    }
}
