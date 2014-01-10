<?php
class SFM_Exception_EntityIntegrity extends Exception
{
    public function __construct(SFM_Entity $entity, $field)
    {
        $class = get_class($entity);
        $message = "Update failed. Entity {$class} has no field `{$field}`";
        parent::__construct($message);
    }
}