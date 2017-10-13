<?php
namespace LogAnalyzer\Items;

interface ItemInterface
{
    /**
     * check exist of key.
     * @param $key
     * @return bool
     */
    public function have($key);

    /**
     * get all keys.
     * @return array
     */
    public function keys();

    /**
     * get data.
     * @param $key
     * @return mixed
     */
    public function get($key);
}
