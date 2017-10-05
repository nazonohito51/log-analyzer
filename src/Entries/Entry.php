<?php
namespace LogAnalyzer\Entries;

class Entry implements EntryInterface
{
    private $property = [];

    public function __construct($iterable)
    {
        foreach ($iterable as $key => $value) {
            $this->property[$key] = $value;
        }
    }

    public function getProperties()
    {
        return array_keys($this->property);
    }

    public function __get($name)
    {
        if (isset($this->property[$name])) {
            return $this->property[$name];
        }

        return null;
    }
}
