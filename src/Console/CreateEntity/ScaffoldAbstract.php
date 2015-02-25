<?php
namespace SFM\Console\CreateEntity;

abstract class ScaffoldAbstract
{
    protected $class;
    protected $table;
    protected $entityClass;
    protected $mapperClass;
    protected $aggregateClass;
    protected $criteriaClass;
    protected $queryBuilderClass;

    public function __construct($table, $class)
    {
        $this->table = $table;

        $this->class = $class;

        $this->entityClass       = "Entity_{$this->class}";
        $this->mapperClass       = "Mapper_{$this->class}";
        $this->aggregateClass    = "Aggregate_{$this->class}";
        $this->criteriaClass     = "Criteria_{$this->class}";
        $this->queryBuilderClass = "QueryBuilder_{$this->class}";
    }

    abstract public function getScaffold();

    /**
     * @return string
     */
    public function getFilename()
    {
        $path = $this->getType() . DIRECTORY_SEPARATOR .
            str_replace(" ", DIRECTORY_SEPARATOR, ucwords(str_replace("_", " ", $this->table))) . ".php";

        return $path;
    }

    public function getClass()
    {
        return "{$this->getType()}_{$this->class}";
    }

    abstract public function getType();
}