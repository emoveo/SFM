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

     /** @var \SFM_Cache */
    protected $cache;

    protected $isStarted = false;

    public function __construct(SFM_Cache_Interface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return bool
     */
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
        $this->isStarted = false;

        foreach ($this->log as $expiration => $items) {
            $this->cache->setMulti($items, $expiration);
        }

        foreach ($this->rawLog as $key => $logItem) {
            $this->cache->setRaw($key, $logItem['value'], $logItem['expiration']);
        }
    }
    
    public function rollback()
    {
        $this->isStarted = false;

        foreach ($this->resetableLog as $value) {

            /** @var $object SFM_Transaction_Restorable */
            $object = $value['object'];
            $object->restoreObjectState($value['state']);
        }
    }
    
    public function logBusiness(SFM_Business $value)
    {
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
     * @param SFM_Business[] $items
     * @param int $expiration
     */
    public function logMulti(array $items, $expiration = 0)
    {
        foreach ($items as $obj) {
            $this->log[$expiration][] = $obj;
        }
    }
    
    public function getLog()
    {
        return $this->log;
    }
}
