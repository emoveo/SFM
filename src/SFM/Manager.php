<?php

class SFM_Manager extends Pimple
{
    protected static $instance;

    /**
     * @return SFM_Manager
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            $container = new SFM_Manager();
            $container->reset();

            self::$instance = $container;
        }

        return self::$instance;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this['db'] = $this->share(function () {
            $db = new SFM_DB();
            $db->init($this['db_config']);
            $db->connect();

            return $db;
        });

        $this['cacheMemory'] = $this->share(function () {
            $cache = new \SFM\Cache\CacheProvider();
            $cache->init($this['cache_config']);
            $cache->connect();

            return $cache;
        });

        $this['cacheSession'] = $this->share(function () {
            return new \SFM\Cache\Session();
        });

        $this['identityMap'] = $this->share(function () {
            return new SFM_IdentityMap();
        });

        $this['transaction'] = $this->share(function () {
            $transaction = new SFM_Transaction();
            $transaction->addTransactionEngine($this->getDb());
            $transaction->addTransactionEngine($this->getCache());
            $transaction->addTransactionEngine($this->getIdentityMap());

            return $transaction;
        });

        $this['repository'] = $this->share(function () {
            return new \SFM\Repository();
        });

        return $this;
    }

    /**
     * @return \SFM\Repository
     */
    public function getRepository()
    {
        return $this["repository"];
    }

    /**
     * @return SFM_DB
     */
    public function getDb()
    {
        return $this['db'];
    }

    /**
     * @return SFM\Cache\CacheProvider
     */
    public function getCache()
    {
        return $this['cacheMemory'];
    }

    /**
     * @return \SFM\Cache\Session
     */
    public function getCacheSession()
    {
        return $this['cacheSession'];
    }

    /**
     * @return SFM_IdentityMap
     */
    public function getIdentityMap()
    {
        return $this['identityMap'];
    }

    /**
     * @return SFM_Transaction
     */
    public function getTransaction()
    {
        return $this['transaction'];
    }
}