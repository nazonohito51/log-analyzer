<?php
namespace LogAnalyzer\Database\Column;

use LogAnalyzer\Database\Column\ColumnInterface;
use LogAnalyzer\Database\Column\FileStorageColumn\ValueStore;

class FileStorageColumn implements ColumnInterface
{
    private $file;
    protected $itemIds = [];
    protected $values = [];
    protected $loaded = true;
    /**
     * @var ValueStore
     */
    private $valueStore;

    public function __construct($saveDir, ValueStore $valueStore, array $data = [])
    {
        if (!file_exists($saveDir)) {
            throw new \InvalidArgumentException();
        }

        $this->file = new \SplFileObject($this->getSavePath($saveDir), 'w+');

        foreach ($data as $value => $itemIds) {
            foreach ($itemIds as $itemId) {
                $this->addData($value, $itemId);
            }
        }
        $this->valueStore = $valueStore;
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
        return array_keys($this->itemIds, $this->getValueNo($value));
    }

    public function getValue($itemId)
    {
        $valueNo = $this->itemIds[$itemId];
        return $this->getValueKey($valueNo);
    }

    public function getValues()
    {
        return array_values($this->values);
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
            $valueKey = $this->getValueKey($valueNo);

            if (!isset($ret[$valueKey])) {
                $ret[$valueKey] = [];
            }
            $ret[$valueKey][] = $itemId;
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

        $this->itemIds[$itemId] = $this->getValueNo($value);
    }

    protected function load()
    {
        $this->file->rewind();
        $this->itemIds = unserialize($this->file->fread($this->file->getSize()));
        $this->loaded = true;
    }

    protected function getValueKey($valueNo)
    {
        return isset($this->values[$valueNo]) ? $this->values[$valueNo] : null;
    }

    protected function getValueNo($value)
    {
        $keys = array_keys($this->values, $value);

        return count($keys) === 1 ? $keys[0] : $this->addValue($value);
    }

    protected function addValue($value)
    {
        $this->values[] = $value;

        return count($this->values) - 1;
    }
}
