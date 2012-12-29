<?php

/**
 * Manage transactions on DB and Cache Layers simultaneously
 *
 * @author andry
 */
class SFM_Transaction 
{
    public static function begin()
    {
        SFM_DB::getInstance()->beginTransaction();
        SFM_Cache_Memory::getInstance()->beginTransaction();
    }   
    
    public static function commit()
    {
        SFM_DB::getInstance()->commit();
        SFM_Cache_Memory::getInstance()->commitTransaction();
    }
    
    public static function rollback()
    {
        SFM_DB::getInstance()->rollback();
        SFM_Cache_Memory::getInstance()->rollbackTransaction();
    }
}
