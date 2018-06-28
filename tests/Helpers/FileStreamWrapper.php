<?php
namespace Tests\LogAnalyzer\Helpers;

class FileStreamWrapper
{
    const STREAM_PROTOCOL = 'mywrapper';

    protected static $body;

    protected $position;

    public static function enable($body)
    {
        self::$body = $body;
        stream_wrapper_register(self::STREAM_PROTOCOL, __CLASS__);
    }

    public static function disable()
    {
        self::$body = null;
        stream_wrapper_unregister(self::STREAM_PROTOCOL);
    }

    function stream_open($path, $mode, $options, &$opened_path)
    {
        return true;
    }

    function stream_read(int $count)
    {
        $ret = substr(self::$body, $this->position, $count);
        $this->position += strlen($ret);

        return $ret;
    }

    function stream_eof()
    {
        return $this->position >= strlen(self::$body);
    }

    /**
     * To answer the call to file_exists()
     * @param $path
     * @param $flags
     * @return array
     */
    public function url_stat($path, $flags)
    {
        return stat(__FILE__);
    }

    public function stream_seek($offset, $whence = SEEK_SET)
    {
        $this->position = 0;
        return true;
    }

    public function stream_tell()
    {
        return $this->position;
    }
}
