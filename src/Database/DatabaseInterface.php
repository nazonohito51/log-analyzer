<?php
namespace LogAnalyzer\Database;

interface DatabaseInterface
{
    public function addColumn($key, $value, $itemId);
}
