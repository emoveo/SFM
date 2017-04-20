<?php
namespace SFM\Console\CreateEntity;

class QueryBuilderScaffold extends ScaffoldAbstract
{
    public function getScaffold()
    {
        $scaffold = <<<EOD
<?php

class {$this->queryBuilderClass} extends \SFM\QueryBuilder\AbstractQueryBuilder
{
    protected \$conditions = [];

    public function __construct({$this->criteriaClass} \$criteria)
    {
        \$this->criteria = \$criteria;
    }
    
    protected function setup()
    {
        \$this->sql = "SELECT id FROM {$this->table}";
        if (\$this->conditions) {
            \$this->sql .= ' WHERE ' . implode(' AND ', \$this->conditions);
        }
    }
}

EOD;

        return $scaffold;
    }

    public function getType()
    {
        return 'QueryBuilder';
    }
}