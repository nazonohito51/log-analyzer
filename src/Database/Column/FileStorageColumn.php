<?php
namespace LogAnalyzer\Database\Column;

use LogAnalyzer\Database\Column\FileStorageColumn\ValueStore;

class FileStorageColumn implements ColumnInterface
{
    protected $file;
    protected $items = [];
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

    public function getItemIds($value)
    {
        return array_keys($this->getItems(), $this->values->getValueNo($value));
    }

    public function getValue($itemId)
    {
        $valueNo = $this->getItems()[$itemId];
        return $this->values->get($valueNo);
    }

    public function getValues()
    {
        return $this->values->getAll();
    }

    public function getSubset(array $itemIds)
    {
        $ret = [];
        $thisItemIds = $this->getItems();

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
        if ($this->file->fwrite(serialize($this->items)) === 0) {
            return false;
        }
        $this->items = [];
        $this->loaded = false;

        return true;
    }

    protected function getItems()
    {
        if ($this->loaded === false) {
            $this->load();
        }

        return $this->items;
    }

    protected function addData($value, $itemId)
    {
        if ($this->loaded === false) {
            $this->load();
        }

        $this->items[$itemId] = $this->values->getValueNo($value);
    }

    protected function load()
    {
        $this->file->rewind();
        $this->items = unserialize($this->file->fread($this->file->getSize()));
        $this->loaded = true;
    }
}
