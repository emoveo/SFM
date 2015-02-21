<?php
/**
 * @method Entity_Mock getEntityById() getEntityById(int $id)
 */
class Mapper_Mock extends \SFM\Mapper
{
    /**
     * @var Mapper_Mock
     */
    protected static $instance;

    protected $tableName = 'mock';
}