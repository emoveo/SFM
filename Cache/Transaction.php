<?php

/**
 * Manage transactions on Cache Layer
 *
 * @author andry
 */
class SFM_Cache_Transaction 
{
    /**
     * Transaction log
     * @var array 
     */
    protected $log;
    
     /**
     * Memcached object
     * 
     * @var Memcached
     */
    protected $driver;
    
    protected $isStarted = false;


    public function __construct( $driver )
    {
        $this->driver = $driver;
    }
    
    public function isStarted()
    {
        return $this->isStarted;
    }
    
    public function begin()
    {
        $this->isStarted = true;
        $this->log = array();
    }
    
    public function commit()
    {
        foreach ( $this->log as $expiration=>$val ) {
            $this->driver->setMulti($val, $expiration);
        }
    }
    
    public function rollback()
    {
        ;//do nothing
    }
    
    public function log(SFM_Business $value)
    {
        //group by expire for simple multiSet
        $this->log[$value->getExpires()][] =  $value;
    }
    
    /*
     * @param array[SFM_Entities] $items
     * @param int $expiration
     */
    public function logMulti(array $items, $expiration=0)
    {
        //group by expire for simple multiSet
        foreach ( $items as $obj ) {
            $this->log[$expiration][] = $obj;
        }
    }
    
    public function getLog()
    {
        return $this->log;
    }
}
