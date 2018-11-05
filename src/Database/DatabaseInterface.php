<?php
namespace LogAnalyzer\Database;

interface DatabaseInterface
{
    public function addColumn($key, $value, $itemId);
    public function get($key, $value);
    public function getValues($key);
}
