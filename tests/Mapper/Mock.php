<?php
/**
 * @method Entity_Mock getEntityById() getEntityById(int $id)
 */
class Mapper_Mock extends SFM_Mapper
{
    /**
     * @var Mapper_Mock
     */
    protected static $instance;

    protected $tableName = 'mock';

    /**
     * Singleton
     *
     * @return Mapper_Mock
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}