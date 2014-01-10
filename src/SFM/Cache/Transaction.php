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

    protected $deletedLog;

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

    /**
     * Begin transaction
     */
    public function begin()
    {
        if ($this->isStarted) {
            throw new SFM_Exception_Transaction("Перед вызовом `begin` необходимо завершить прошлую транзакцию");
        }

        $this->isStarted = true;
        $this->log = array();
        $this->rawLog = array();
        $this->resetableLog = array();
        $this->deletedLog = array();
    }

    /**
     * Commit transaction
     * @throws SFM_Exception_Transaction
     */
    public function commit()
    {
        if (false === $this->isStarted) {
            throw new SFM_Exception_Transaction("Перед вызовом `commit` необходимо начать транзакцию");
        }

        $this->isStarted = false;

        foreach ($this->log as $expiration => $items) {
            $this->cache->setMulti($items, $expiration);
        }

        foreach ($this->rawLog as $key => $logItem) {
            $this->cache->setRaw($key, $logItem['value'], $logItem['expiration']);
        }

        foreach ($this->deletedLog as $key) {
            $this->cache->delete($key);
        }
    }

    /**
     * Rollback transaction
     * @throws SFM_Exception_Transaction
     */
    public function rollback()
    {
        if (false === $this->isStarted) {
            throw new SFM_Exception_Transaction("Перед вызовом `rollback` необходимо начать транзакцию");
        }

        $this->isStarted = false;

        foreach ($this->resetableLog as $value) {

            /** @var $object SFM_Transaction_Restorable */
            $object = $value['object'];
            $object->restoreObjectState($value['state']);
        }
    }

    /**
     * @param SFM_Business $value
     * @param integer|null $expiration
     * @throws SFM_Exception_Transaction
     */
    public function logBusiness(SFM_Business $value, $expiration = null)
    {
        if (is_null($expiration)) {
            $expiration = $value->getExpires();
        }
        
        $this->log[$expiration][$value->getCacheKey()] =  $value;
        $this->cancelDelete($value->getCacheKey());
        $this->logResetable($value);
    }

    public function logDeleted($key)
    {
        $this->deletedLog[] = $key;
        $this->cancelSet($key);
    }

    protected function cancelSet($key)
    {
        if ($key = array_search($key, $this->rawLog)) {
            unset($this->rawLog[$key]);
        }

        foreach ($this->log as $expiration => $items) {

            if (isset($items[$key])) {
                unset($items[$key]);
            }

            $this->log[$expiration] = $items;
        }
    }

    protected function cancelDelete($key)
    {
        if ($key = array_search($key, $this->deletedLog)) {
            unset($this->deletedLog[$key]);
        }
    }

    public function logRaw($key, $value, $expiration = 0)
    {
        $this->rawLog[$key] = array(
            'value'      => $value,
            'expiration' => $expiration
        );

        $this->cancelDelete($key);

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
            $this->logBusiness($obj, $expiration);
        }
    }
    
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Is key deleted during transaction
     * @param $key
     * @return bool
     */
    public function isKeyDeleted($key)
    {
        $isDeleted = in_array($key, $this->deletedLog);

        return $isDeleted;
    }
}
