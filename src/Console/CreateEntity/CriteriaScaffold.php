<?php
namespace SFM\Console\CreateEntity;

class CriteriaScaffold extends ScaffoldAbstract
{
    public function getScaffold()
    {
        $scaffold = <<<EOD
<?php
class {$this->criteriaClass} extends \Criteria\AbstractCriteria
{
    /**
     * @return {$this->queryBuilderClass}
     */
    public function createQueryBuilder()
    {
        \$builder = new {$this->queryBuilderClass}(\$this);
        return \$builder;
    }
}
EOD;

        return $scaffold;
    }

    public function getType()
    {
        return 'Criteria';
    }
}