<?php
declare(strict_types=1);

namespace LogAnalyzer\Collection\Column;

interface ColumnInterface
{
    public function add($value, $itemId): self;
    public function getItemIds($value): array;
    public function getValue($itemId);
    public function getValues(): array;
    public function getSubset(array $itemIds): array;
    public function save(): bool;
    public function delete(): bool;
}
