<?php
namespace LogAnalyzer\Database;

class FileStorageColumn implements ColumnInterface
{
    private $file;
    protected $data = [];
    protected $loaded = true;

    public function __construct($saveDir, array $data = [])
    {
        if (!file_exists($saveDir)) {
            throw new \InvalidArgumentException();
        }

        $this->data = $data;
        $this->file = new \SplFileObject($this->getSavePath($saveDir), 'w');
    }

    protected function getSavePath($dir)
    {
        $objectHash = spl_object_hash($this);
        return substr($dir, -1) === '/' ? $dir . $objectHash : $dir . '/' . $objectHash;
    }

    public function add($value, $itemId)
    {
        $this->addData($value, $itemId);

        return $this;
    }

    public function getItems($value)
    {
        return isset($this->getData()[$value]) ? $this->getData()[$value] : [];
    }

    public function getValue($itemId)
    {
        foreach ($this->getData() as $value => $itemIds) {
            if (in_array($itemId, $itemIds)) {
                return $value;
            }
        }

        return null;
    }

    protected function getData()
    {
        if ($this->loaded === false) {
            $this->load();
        }

        return $this->data;
    }

    protected function addData($value, $itemId)
    {
        if ($this->loaded === false) {
            $this->load();
        }

        isset($this->data[$value]) ? $this->data[$value][] = $itemId : $this->data[$value] = [$itemId];
    }

    public function getValues()
    {
        return array_keys($this->data);
    }

    public function save()
    {
        $this->file->fwrite(serialize($this->data));
        $this->loaded = false;
    }

    protected function load()
    {
        $this->file->rewind();
        $this->data = unserialize($this->file->fread($this->file->getSize()));
        $this->loaded = true;
    }
}
