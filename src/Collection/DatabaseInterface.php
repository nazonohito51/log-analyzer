<?php
declare(strict_types=1);

namespace LogAnalyzer\Collection;

interface DatabaseInterface
{
    public function addValue($itemId, $columnName, $value): void;
    public function getItemIds($columnName, $value): array;
    public function getValue($itemId, $columnName);
    public function getValues($columnName): array;
    public function getSubset(array $itemIds, $columnName): array;
    public function save(): bool;
}
