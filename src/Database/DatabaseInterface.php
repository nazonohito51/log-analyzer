<?php
namespace LogAnalyzer\Database;

interface DatabaseInterface
{
    public function addColumn($key, $value, $itemId);
    public function getColumn($key);
    public function getScheme();
}
