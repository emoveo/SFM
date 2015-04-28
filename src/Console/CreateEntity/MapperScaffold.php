<?php
namespace SFM\Console\CreateEntity;

class MapperScaffold extends ScaffoldAbstract
{
    /**
     * @return string
     */
    public function getScaffold()
    {
        $scaffold = <<<EOD
<?php
/**
 * @method {$this->entityClass} getEntityById() getEntityById(int \$id)
 */
class {$this->mapperClass} extends \SFM\Mapper
{
    protected \$tableName = '{$this->table}';

    /**
     * @param {$this->criteriaClass} \$criteria
     * @return {$this->aggregateClass}
     */
    public function getList({$this->criteriaClass} \$criteria)
    {
        \$builder = new {$this->queryBuilderClass}(\$criteria);
        \$aggregate = \$this->getAggregateBySQL(\$builder->getSQL(), \$builder->getParams());

        return \$aggregate;
    }
}
EOD;

        return $scaffold;
    }

    public function getType()
    {
        return 'Mapper';
    }
}