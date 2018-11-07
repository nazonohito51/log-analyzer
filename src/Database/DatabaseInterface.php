<?php
namespace LogAnalyzer\Database;

interface DatabaseInterface
{
    public function addColumnValue($columnName, $value, $itemId);
    public function getItemIds($columnName, $value);
    public function getValue($columnName, $itemId);
    public function getColumnValues($columnName);
    public function save();
}
