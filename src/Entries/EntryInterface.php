<?php
namespace LogAnalyzer\Entries;

interface EntryInterface
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
