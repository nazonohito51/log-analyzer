<?php
declare(strict_types=1);

namespace LogAnalyzer\Presenter;

use LucidFrame\Console\ConsoleTable as TableView;

class ConsoleTable
{
    private $headers;
    private $matrix;

    public function __construct(array $headers, array $matrix)
    {
        $this->headers = $headers;
        $this->matrix = $matrix;
    }

    public function display($strLen = 60): void
    {
        $table = new TableView();

        foreach ($this->headers as $header) {
            $table->addHeader($header);
        }
        foreach ($this->matrix as $row) {
            $table->addRow();
            foreach ($this->headers as $header) {
                $table->addColumn($this->formatValue($row[$header], $strLen));
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

    public function sort($columnName, $orderByDesc = false): self
    {
        $sortColumn = array_column($this->matrix, $columnName);
        array_multisort($sortColumn, $orderByDesc ? SORT_DESC : SORT_ASC, $this->matrix);

        return $this;
    }
}
