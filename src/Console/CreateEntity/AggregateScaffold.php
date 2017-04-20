<?php
namespace SFM\Console\CreateEntity;

class AggregateScaffold extends ScaffoldAbstract
{
    public function getScaffold()
    {
        $scaffold = <<<EOD
<?php
/**
 * @method {$this->aggregateClass} filter() filter(array \$matches = [], array \$disagrees = [])
 * @method static {$this->aggregateClass} combine() combine({$this->mapperClass} \$mapper, array \$aggregates, \$cacheKey = null)
 * @method {$this->aggregateClass} recircle() recircle(\$offset)
 * @method {$this->entityClass} current() current()
 * @method {$this->entityClass} rewind() rewind()
 * @method {$this->entityClass} next() next()
 * @method {$this->entityClass}[] getContent() getContent()
 * @method {$this->entityClass} getEntityById() getEntityById(\$id)
 */
class {$this->aggregateClass} extends \SFM\Aggregate
{
}
EOD;

        return $scaffold;
    }

    public function getType()
    {
        return 'Aggregate';
    }
}