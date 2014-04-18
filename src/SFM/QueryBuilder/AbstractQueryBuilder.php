<?php
namespace SFM\QueryBuilder;

abstract class AbstractQueryBuilder
{
    protected $criteria;
    protected $sql = null;
    protected $vars = null;

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
    public function getVars()
    {
        if (null === $this->sql) {
            $this->setup();
        }

        return $this->vars;
    }

    abstract protected function setup();
}