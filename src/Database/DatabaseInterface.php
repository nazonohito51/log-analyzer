<?php
declare(strict_types=1);

namespace LogAnalyzer\Database;

interface DatabaseInterface
{
    public function addColumnValue($columnName, $value, $itemId): void;
    public function getItemIds($columnName, $value): array;
    public function getValue($columnName, $itemId);
    public function getColumnValues($columnName): array;
    public function getColumnSubset($columnName, array $itemIds): array;
    public function save(): bool;
}
