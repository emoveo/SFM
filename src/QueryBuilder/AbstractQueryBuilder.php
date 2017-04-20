<?php
namespace SFM\QueryBuilder;

abstract class AbstractQueryBuilder
{
    protected $criteria;
    protected $sql = null;
    protected $params = array();

    /**
     * @return string
     */
    public function getSQL()
    {
        if (null === $this->sql) {
            $this->setup();
        }

        return $this->sql;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        if (null === $this->sql) {
            $this->setup();
        }

        return $this->params;
    }

    abstract protected function setup();
}
