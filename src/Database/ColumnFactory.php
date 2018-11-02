<?php
namespace LogAnalyzer\Database;

class ColumnFactory
{
    public function build()
    {
        return new InMemoryColumn();
    }
}
