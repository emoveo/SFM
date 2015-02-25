<?php
namespace SFM\Console\CreateEntity;

class EntityScaffold extends ScaffoldAbstract
{
    public function getScaffold()
    {
        $scaffold = <<<EOD
<?php
class {$this->entityClass} extends \SFM\Entity
{
    /**
    *   @return int
    **/
    public function getId()
    {
        return (int)\$this->id;
    }
}
EOD;

        return $scaffold;
    }

    public function getType()
    {
        return 'Entity';
    }
}