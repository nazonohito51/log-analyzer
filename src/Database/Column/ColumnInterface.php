<?php
namespace LogAnalyzer\Database\Column;

interface ColumnInterface
{
    public function add($value, $itemId);
    public function getItemIds($value);
    public function getValue($itemId);
    public function getValues();
    public function getSubset(array $itemIds);
    public function save();
}
