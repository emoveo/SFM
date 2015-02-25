<?php
namespace SFM\Console\CreateEntity;

class QueryBuilderScaffold extends ScaffoldAbstract
{
    public function getScaffold()
    {
        $scaffold = <<<EOD
<?php
class {$this->queryBuilderClass} extends \QueryBuilder\AbstractQueryBuilder
{
    protected \$conditions = array();
    protected \$sql = "SELECT id FROM {$this->table}";

    public function __construct({$this->criteriaClass} \$criteria)
    {

        if (\$this->sql && \$this->conditions) {
            \$this->sql .= ' WHERE ' . implode(' AND ', \$this->conditions);
        }

        parent::__construct(\$criteria);
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