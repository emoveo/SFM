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
        $this->prepareParams();

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
        $this->prepareParams();

        return $this->params;
    }
    
    protected function prepareParams()
    {
        if ($this->params) {
            $i = 1;
            // replace according to $1, $2, $3 notation
            foreach ($this->params as $param => $_value) {
                $this->sql = str_replace(':' . $param, '$' . $i++, $this->sql);
            }
        }
    }

    abstract protected function setup();
}
