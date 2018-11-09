<?php
declare(strict_types=1);

namespace LogAnalyzer\View;

use LucidFrame\Console\ConsoleTable;

class TableView
{
    private $headers;
    private $matrix;

    public function __construct(array $headers, array $matrix)
    {
        $this->headers = $headers;
        $this->matrix = $matrix;
    }

    public function display($strLen = null): void
    {
        $table = new ConsoleTable();

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
}
