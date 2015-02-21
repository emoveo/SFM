<?php
namespace SFM\Cache\Transaction;

use SFM\Cache\CacheException;
use SFM\Cache\CacheProvider;
use \SFM\Transaction\TransactionEngine;
use \SFM\Transaction\RestorableInterface;
use \SFM\Business;

class TransactionCache implements TransactionEngine
{
    /** @var array Entity transaction log */
    protected $log;

    /** @var array Mixed values transaction log */
    protected $rawLog;

    /** @var array Resetable objects */
    protected $resetableLog;

    protected $deletedLog;

     /** @var CacheProvider */
    protected $cache;

    protected $isStarted = false;

    /**
     * @param CacheProvider $cache
     */
    public function __construct(CacheProvider $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return bool
     */
    public function isTransaction()
    {
        return $this->isStarted;
    }

    /**
     * Begin transaction
     * @return bool
     * @throws CacheException
     */
    public function beginTransaction()
    {
        if ($this->isStarted) {
            throw new CacheException("SFM/Cache: attempt to begin transaction before active transaction was complete");
        }

        $this->isStarted = true;
        $this->log = array();
        $this->rawLog = array();
        $this->resetableLog = array();
        $this->deletedLog = array();
    }

    /**
     * Commit transaction
     * @return bool
     * @throws CacheException
     */
    public function commitTransaction()
    {
        if (false === $this->isStarted) {
            throw new CacheException("SFM/Cache: attempt to commit transaction but no transaction was started");
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

        return true;
    }

    /**
     * Rollback transaction
     * @return bool
     * @throws CacheException
     */
    public function rollbackTransaction()
    {
        if (false === $this->isStarted) {
            throw new CacheException("SFM/Cache: attempt to rollback transaction but no transaction was started");
        }

        $this->isStarted = false;

        foreach ($this->resetableLog as $value) {

            /** @var $object RestorableInterface */
            $object = $value['object'];
            $object->restoreObjectState($value['state']);
        }

        return true;
    }

    /**
     * @param Business $value
     * @param integer|null $expiration
     */
    public function logBusiness(Business $value, $expiration = null)
    {
        if (is_null($expiration)) {
            $expiration = $value->getExpires();
        }
        
        $this->log[$expiration][$value->getCacheKey()] =  $value;
        $this->cancelDelete($value->getCacheKey());
        $this->logResetable($value);
    }

    /**
     * @param string $key
     */
    public function logDeleted($key)
    {
        $this->deletedLog[] = $key;
        $this->cancelSet($key);
    }

    /**
     * @param string $key
     */
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

    /**
     * @param string $key
     */
    protected function cancelDelete($key)
    {
        if ($key = array_search($key, $this->deletedLog)) {
            unset($this->deletedLog[$key]);
        }
    }

    /**
     * @param string $key
     * @param string $value
     * @param int $expiration
     */
    public function logRaw($key, $value, $expiration = 0)
    {
        $this->rawLog[$key] = array(
            'value'      => $value,
            'expiration' => $expiration
        );

        $this->cancelDelete($key);
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getRaw($key)
    {
        if (in_array($key, $this->deletedLog)) {
            $value = null;
        } else {
            $value = isset($this->rawLog[$key]) ? $this->rawLog[$key] : null;
        }

        return $value;
    }

    /**
     * @param RestorableInterface $value
     */
    public function logResetable(RestorableInterface $value)
    {
        if (false === isset($this->resetableLog[$value->getObjectIdentifier()])) {
            $this->resetableLog[$value->getObjectIdentifier()] = array('object' => $value, 'state' => $value->getObjectState());
        }
    }
    
    /**
     * @param Business[] $items
     * @param int $expiration
     */
    public function logMulti(array $items, $expiration = 0)
    {
        foreach ($items as $obj) {
            $this->logBusiness($obj, $expiration);
        }
    }

    /**
     * @param array $items
     * @param int $expiration
     * @return bool
     */
    public function logRawMulti(array $items, $expiration = 0)
    {
        foreach ($items as $key => $value) {
            $this->logRaw($key, $value, $expiration);
        }

        return true;
    }

    /**
     * @return array
     */
    public function getLog()
    {
        return $this->log;
    }
}
