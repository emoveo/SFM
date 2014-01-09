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
     * @return SFM_Cache_Memory
     */
    public function getCacheMemory()
    {
        if (null === $this->cacheMemory) {
            $this->cacheMemory = new SFM_Cache_Memory();
        }

        return $this->cacheMemory;
    }

    /**
     * @return SFM_Cache_File
     */
    public function getCacheFile()
    {
        if (null === $this->cacheFile) {
            $this->cacheFile = new SFM_Cache_File();
        }

        return $this->cacheFile;
    }

    /**
     * @return SFM_Cache_Session
     */
    public function getCacheSession()
    {
        if (null === $this->cacheSession) {
            $this->cacheSession = new SFM_Cache_Session();
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
        }

        return $this->transaction;
    }
}