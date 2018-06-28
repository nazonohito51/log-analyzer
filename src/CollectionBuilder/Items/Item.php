<?php
namespace LogAnalyzer\CollectionBuilder\Items;

use LogAnalyzer\CollectionBuilder\LogFiles\LogFile;

class Item implements ItemInterface
{
    private $file;
    private $linePos;

    public function __construct(LogFile $file, $linePos)
    {
        $this->file = $file;
        $this->linePos = $linePos;
    }

    public function getLinePos()
    {
        return $this->linePos;
    }

    public function have($key)
    {
        $content = $this->getContent();
        return isset($content[$key]);
    }

    public function keys()
    {
        $content = $this->getContent();
        return array_keys($content);
    }

    public function get($key)
    {
        $content = $this->getContent();
        return isset($content[$key]) ? $content[$key] : null;
    }

    private function getContent()
    {
        $this->file->seek($this->linePos);
        return $this->file->current();
    }
}
