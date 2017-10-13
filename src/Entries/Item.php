<?php
namespace LogAnalyzer\Entries;

class Item implements EntryInterface
{
    private $data = [];

    public function __construct($iterable)
    {
        foreach ($iterable as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    public function have($key)
    {
        return isset($this->data[$key]) ? true : false;
    }

    public function keys()
    {
        return array_keys($this->data);
    }

    public function get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return null;
    }
}
