<?php
namespace LogAnalyzer\Database;

class InMemoryDatabase implements DatabaseInterface
{
    /**
     * @var ColumnInterface[]
     */
    protected $columns = [];
    /**
     * @var ColumnFactory
     */
    protected $factory;

    public function __construct(ColumnFactory $factory, array $columns = [])
    {
        $this->columns = $columns;
        $this->factory = $factory;
    }

    public function addColumn($key, $value, $itemId)
    {
        $this->isExistColumn($key) ?
            $this->columns[$key]->add($value, $itemId) :
            $this->columns[$key] = $this->factory->build()->add($value, $itemId);
    }

    protected function isExistColumn($key)
    {
        return isset($this->columns[$key]);
    }

    public function get($key, $value)
    {
        return $this->isExistColumn($key) ? $this->columns[$key]->getItems($value) : null;
    }
}
