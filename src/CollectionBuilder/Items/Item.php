<?php
namespace LogAnalyzer\CollectionBuilder\Items;

use LogAnalyzer\CollectionBuilder\Items\ItemInterface;

class Item implements ItemInterface
{
    private $data = [];
    protected $keys = [];

    public function __construct($iterable)
    {
        foreach ($iterable as $key => $value) {
            $this->addData($key, $value);
        }

        if (empty($this->keys)) {
            $this->keys = array_keys($this->data);
        }
    }

    private function addData($key, $value)
    {
        if (!empty($this->keys) && !in_array($key, $this->keys)) {
            return;
        }

        $this->data[$key] = $value;
    }

    public function have($key)
    {
        return isset($this->data[$key]) ? true : false;
    }

    public function keys()
    {
        return $this->keys;
    }

    public function get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return null;
    }
}
