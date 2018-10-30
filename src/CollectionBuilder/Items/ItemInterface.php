<?php
namespace LogAnalyzer\CollectionBuilder\Items;

use LogAnalyzer\CollectionBuilder\LogFiles\LogFile;

interface ItemInterface
{
    /**
     * @param LogFile $file
     * @param string $linePos
     */
    public function __construct(LogFile $file, $linePos);

    /**
     * @return LogFile
     */
    public function getLogFile();

    /**
     * get line pos in file.
     * @return integer
     */
    public function getLinePos();

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
