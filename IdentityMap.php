<?php
/**
 * Identity Map for already registered objects
 *
 */
class SFM_IdentityMap {

    private static $map = array();
    private static $enabled = true;
    
    /**
     * Saves Object
     *
     * @param SFM_Entity $obj
     */
    public static function addEntity(SFM_Entity $entity)
    {
        if(self::$enabled) {
            $className = get_class($entity);
            if (!isset(self::$map[$className])) {
                self::$map[$className] = array();
            }
            
            if($entity->id !== null){
                self::$map[$className][$entity->id] = $entity;
            }
        }
    }
    
    /**
     * Return SFM_Entity from map
     *
     * @param string $className
     * @param int $id
     * @return SFM_Entity|null
     */
    public static function getEntity($className, $id)
    {
        if(isset(self::$map[$className][$id])) {
            return self::$map[$className][$id];
        } else {
            return null;
        }
    }
    
    public static function getContents()
    {
        return self::$map;
    }
    
    public static function deleteEntity(SFM_Entity $entity)
    {
        $className = get_class($entity);
        self::$map[$className][$entity->id] = null;
    }
    
    public static function enable()
    {
        self::$enabled = true;
    }
    
    public static function disable()
    {
        self::$enabled = false;
    }
}

?>