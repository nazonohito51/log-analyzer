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

    protected function defaultColumn()
    {
        return new InMemoryColumn();
    }

    public function getColumn($key)
    {
        return isset($this->columns[$key]) ?? null;
    }

    public function getScheme()
    {

    }
}
