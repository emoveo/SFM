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
    /**
     * @var {$this->mapperClass}
     */
    protected static \$instance;
    protected \$tableName = '{$this->table}';

    /**
     * @return {$this->entityClass}
     */
    public function add()
    {
        \$proto = array(
        );
        \$entity = \$this->insertEntity(\$proto);
        return \$entity;
    }

    /**
     * @param {$this->criteriaClass} \$criteria
     * @return {$this->aggregateClass}
     */
    public function getUncachedList({$this->criteriaClass} \$criteria)
    {
        \$builder = new {$this->queryBuilderClass}(\$criteria);

        \$aggregate = \$this->getAggregateBySQL(\$builder->getSQL(), \$builder->getParams());
        if(\$criteria->getPage()){
            \$aggregate->loadEntitiesForCurrentPage(\$criteria->getPage(),\$criteria->getPerPage());
        } else {
            \$aggregate->loadEntities();
        }

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