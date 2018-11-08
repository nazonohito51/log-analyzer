<?php
namespace LogAnalyzer\Database\Column;

use LogAnalyzer\Database\Column\ColumnInterface;
use LogAnalyzer\Database\Column\FileStorageColumn\ValueStore;

class FileStorageColumn implements ColumnInterface
{
    protected $file;
    protected $itemIds = [];
    protected $values;
    protected $loaded = true;

    public function __construct($saveDir, ValueStore $valueStore, array $initialData = [])
    {
        if (!file_exists($saveDir)) {
            throw new \InvalidArgumentException();
        }

        $this->file = new \SplFileObject($this->getSavePath($saveDir), 'w+');
        $this->values = $valueStore;

        foreach ($initialData as $value => $itemIds) {
            foreach ($itemIds as $itemId) {
                $this->addData($value, $itemId);
            }
        }
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
        return array_keys($this->getItemIds(), $this->values->getValueNo($value));
    }

    public function getValue($itemId)
    {
        $valueNo = $this->getItemIds()[$itemId];
        return $this->values->get($valueNo);
    }

    public function getValues()
    {
        return $this->values->getAll();
    }

    public function getSubset(array $itemIds)
    {
        $ret = [];
        $thisItemIds = $this->getItemIds();

        foreach ($itemIds as $itemId) {
            if (!isset($thisItemIds[$itemId])) {
                continue;
            }

            $valueNo = $thisItemIds[$itemId];
            $value = $this->values->get($valueNo);

            $ret[$value][] = $itemId;
        }

        return $ret;
    }

    public function save()
    {
        if ($this->file->fwrite(serialize($this->itemIds)) === 0) {
            return false;
        }
        $this->itemIds = [];
        $this->loaded = false;

        return true;
    }

    protected function getItemIds()
    {
        if ($this->loaded === false) {
            $this->load();
        }

        return $this->itemIds;
    }

    protected function addData($value, $itemId)
    {
        if ($this->loaded === false) {
            $this->load();
        }

        $this->itemIds[$itemId] = $this->values->getValueNo($value);
    }

    protected function load()
    {
        $this->file->rewind();
        $this->itemIds = unserialize($this->file->fread($this->file->getSize()));
        $this->loaded = true;
    }
}
