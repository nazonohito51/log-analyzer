<?php
declare(strict_types=1);

namespace LogAnalyzer\Presenter;

use LogAnalyzer\Exception\InvalidArgumentException;
use LogAnalyzer\View\ColumnStrategyInterface;
use LucidFrame\Console\ConsoleTable as TableView;

class ConsoleTable
{
    private $strategies;
    private $matrix;
    private $rowLimit;

    /**
     * @param ColumnStrategyInterface[] $strategies
     * @param array $matrix
     */
    public function __construct(array $strategies, array $matrix)
    {
        $this->strategies = $strategies;
        $this->matrix = $matrix;
    }

    public function display(int $strLen = 60): void
    {
        if (count($this->matrix) == 0) {
            echo "There is no data to show\n";
            return;
        }

        $table = new TableView();

        foreach ($this->strategies as $strategy) {
            $table->addHeader($strategy->name());
        }
        foreach ($this->matrix as $cnt => $row) {
            if (!is_null($this->rowLimit) && $this->rowLimit <= $cnt) {
                break;
            }

            $table->addRow();
            foreach ($this->strategies as $strategy) {
                $table->addColumn($this->formatValue($row[$strategy->name()], $strLen));
            }
        }

        $table->display();
    }

    protected function formatValue($value, $max = null): string
    {
        if (is_array($value)) {
            $value = $this->arrayToString($value);
        }
        $value = (string)$value;

        if ($max && strlen($value) > $max) {
            $value = substr($value, 0, $max) . '...';
        }

        return $value;
    }

    protected function arrayToString(array $arr): string
    {
        $str = implode(', ', $arr);

        return count($arr) > 1 ? "[{$str}]" : $str;
    }

    public function sort(string $columnName, $orderByDesc = false): self
    {
        $sortColumn = array_column($this->matrix, $columnName);
        array_multisort($sortColumn, $orderByDesc ? SORT_DESC : SORT_ASC, $this->matrix);

        return $this;
    }

    public function limit(int $limit)
    {
        if ($limit <= 0) {
            throw new InvalidArgumentException('limit must be greater than 0.');
        }

        $this->rowLimit = $limit;
    }
}
