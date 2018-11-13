<?php
declare(strict_types=1);

namespace LogAnalyzer\Collection\Column;

interface ColumnInterface
{
    public function getItemIds($value): array;
    public function getValue($itemId);
    public function getValues(): array;
    public function getSubset(array $itemIds): array;

    public function add($itemId, $value): self;
    public function freeze(): self;
    public function save(string $path): bool;
    public function delete(): bool;
}
