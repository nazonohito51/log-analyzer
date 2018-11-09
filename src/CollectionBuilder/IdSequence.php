<?php
namespace LogAnalyzer\CollectionBuilder;

class IdSequence
{
    private static $instance = null;

    private $id = 0;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        return self::$instance ?? self::$instance = new IdSequence();
    }

    public function now()
    {
        return $this->id;
    }

    public function update()
    {
        return ++$this->id;
    }
}