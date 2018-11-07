<?php
namespace LogAnalyzer\Database;

interface ColumnInterface
{
    public function add($value, $itemId);
    public function getItems($value);
    public function getValue($itemId);
    public function getValues();
    public function save();
}
