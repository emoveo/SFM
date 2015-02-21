<?php
namespace SFM;

use SFM\Database\DatabaseProvider;
use SFM\Cache\CacheProvider;
use SFM\Cache\Session;
use SFM\IdentityMap\IdentityMap;
use SFM\Transaction\Transaction;

class Manager extends \Pimple
{
    protected static $instance;

    /**
     * @return Manager
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            $container = new Manager();
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
            $db = new DatabaseProvider();
            $db->init($this['db_config']);
            $db->connect();

            return $db;
        });

        $this['cacheMemory'] = $this->share(function () {
            $cache = new CacheProvider();
            $cache->init($this['cache_config']);
            $cache->connect();

            return $cache;
        });

        $this['cacheSession'] = $this->share(function () {
            return new Session();
        });

        $this['identityMap'] = $this->share(function () {
            return new IdentityMap();
        });

        $this['transaction'] = $this->share(function () {
            $transaction = new Transaction();
            $transaction->addTransactionEngine($this->getDb());
            $transaction->addTransactionEngine($this->getCache());
            $transaction->addTransactionEngine($this->getIdentityMap());

            return $transaction;
        });

        $this['repository'] = $this->share(function () {
            return new Repository();
        });

        return $this;
    }

    /**
     * @return Repository
     */
    public function getRepository()
    {
        return $this["repository"];
    }

    /**
     * @return DatabaseProvider
     */
    public function getDb()
    {
        return $this['db'];
    }

    /**
     * @return CacheProvider
     */
    public function getCache()
    {
        return $this['cacheMemory'];
    }

    /**
     * @return Session
     */
    public function getCacheSession()
    {
        return $this['cacheSession'];
    }

    /**
     * @return IdentityMap
     */
    public function getIdentityMap()
    {
        return $this['identityMap'];
    }

    /**
     * @return Transaction
     */
    public function getTransaction()
    {
        return $this['transaction'];
    }
}