<?php
namespace LogAnalyzer\CollectionBuilder;

class IdSequence
{
    private $id = 1;

    public function __construct($initialId = 1)
    {
        $this->id = $initialId;
    }

    public function now()
    {
        return $this->id;
    }

    public function update()
    {
        $this->id++;

        return $this;
    }
}