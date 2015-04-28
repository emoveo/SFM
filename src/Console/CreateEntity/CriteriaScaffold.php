<?php
namespace SFM\Console\CreateEntity;

class CriteriaScaffold extends ScaffoldAbstract
{
    public function getScaffold()
    {
        $scaffold = <<<EOD
<?php
class {$this->criteriaClass} extends \SFM\Criteria\AbstractCriteria
{

}
EOD;

        return $scaffold;
    }

    public function getType()
    {
        return 'Criteria';
    }
}