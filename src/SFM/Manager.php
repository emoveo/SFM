<?php

class SFM_Manager
{
    protected static $instance;

    /**
     * @return SFM_Manager
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new SFM_Manager();
        }

        return self::$instance;
    }

    protected $db;
    protected $cacheMemory;
    protected $cacheFile;
    protected $cacheSession;
    protected $identityMap;
    protected $transaction;
    protected $monitor;

    /**
     * @return $this
     */
    public function reset()
    {
        $this->db = null;
        $this->cacheMemory = null;
        $this->cacheFile = null;
        $this->cacheSession = null;
        $this->identityMap = null;
        $this->transaction = null;
        $this->monitor = null;

        return $this;
    }

    /**
     * @return SFM_DB
     */
    public function getDb()
    {
        if (null === $this->db) {
            $this->db = new SFM_DB();
        }

        return $this->db;
    }

    /**
     * @return SFM\Cache\CacheProvider
     */
    public function getCache()
    {
        if (null === $this->cacheMemory) {
            $this->cacheMemory = new SFM\Cache\CacheProvider();
        }

        return $this->cacheMemory;
    }

    /**
     * @return \SFM\Cache\Session
     */
    public function getCacheSession()
    {
        if (null === $this->cacheSession) {
            $this->cacheSession = new \SFM\Cache\Session();
        }

        return $this->cacheSession;
    }

    /**
     * @return SFM_IdentityMap
     */
    public function getIdentityMap()
    {
        if (null === $this->identityMap) {
            $this->identityMap = new SFM_IdentityMap();
        }

        return $this->identityMap;
    }

    public function getTransaction()
    {
        if (null === $this->transaction) {
            $this->transaction = new SFM_Transaction();
            $this->transaction->addTransactionEngine($this->getDb());
            $this->transaction->addTransactionEngine($this->getCache());
            $this->transaction->addTransactionEngine($this->getIdentityMap());
        }

        return $this->transaction;
    }
}