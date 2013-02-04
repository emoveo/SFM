<?php

/**
 * Manage transactions on Cache Layer
 *
 * @author andry
 */
class SFM_Cache_Transaction 
{
    /** @var array Entity transaction log */
    protected $log;

    /** @var array Mixed values transaction log */
    protected $rawLog;

    /** @var array Resetable objects */
    protected $resetableLog;

     /**
     * @var Memcached Memcached object
     */
    protected $driver;
    
    protected $isStarted = false;

    public function __construct($driver)
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
        $this->rawLog = array();
        $this->resetableLog = array();
    }
    
    public function commit()
    {
        foreach ( $this->log as $expiration=>$val ) {
            $this->driver->setMulti($val, $expiration);
        }

        foreach ($this->rawLog as $key => $logItem) {
            $this->driver->set($key, $logItem['value'], $logItem['expiration']);
        }
    }
    
    public function rollback()
    {
        foreach ($this->resetableLog as $value) {
            $value['object']->restoreObjectState($value['state']);
        }
    }
    
    public function logBusiness(SFM_Business $value)
    {
        //group by expire for simple multiSet
        $this->log[$value->getExpires()][] =  $value;
        $this->logResetable($value);
    }

    public function logRaw($key, $value, $expiration = 0)
    {
        $this->rawLog[$key] = array(
            'value'      => $value,
            'expiration' => $expiration
        );

        return true;
    }

    public function logResetable(SFM_Transaction_Restorable $value)
    {
        if (false === isset($this->resetableLog[$value->getObjectIdentifier()])) {
            $this->resetableLog[$value->getObjectIdentifier()] = array('object' => $value, 'state' => $value->getObjectState());
        }
    }
    
    /*
     * @param array[SFM_Entities] $items
     * @param int $expiration
     */
    public function logMulti(array $items, $expiration=0)
    {
        //group by expire for simple multiSet
        foreach ($items as $obj) {
            $this->log[$expiration][] = $obj;
        }
    }
    
    public function getLog()
    {
        return $this->log;
    }
}
