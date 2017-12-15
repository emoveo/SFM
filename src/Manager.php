<?php
namespace SFM;

use Pimple\Container;
use SFM\Database\Config;
use SFM\Database\DatabaseProvider;
use SFM\Cache\CacheProvider;
use SFM\Cache\Session;
use SFM\IdentityMap\IdentityMap;
use SFM\IdentityMap\IdentityMapStorage;
use SFM\Transaction\TransactionAggregator;
use SFM\Value\ValueStorage;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Exception\ExceptionInterface;

class Manager extends Container
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
        $this['adapter'] = function() {

                if (!$this->getConfigDb() instanceof Config) {
                    throw new BaseException("DatabaseProvider is not configured");
                }

                try {
                    $adapter = new Adapter(array(
                        'driver' => $this->getConfigDb()->getDriver(),
                        'database' => $this->getConfigDb()->getDb(),
                        'username' => $this->getConfigDb()->getUser(),
                        'password' => $this->getConfigDb()->getPass(),
                        'hostname' => $this->getConfigDb()->getHost(),
                        'port' => $this->getConfigDb()->getPort(),
                    ));

                    if (is_array($this->getConfigDb()->getInitialQueries())) {
                        foreach ($this->getConfigDb()->getInitialQueries() as $query) {
                            $adapter->query($query ,array());
                        }
                    }

                    return $adapter;

                } catch (ExceptionInterface $e) {
                    throw new BaseException('Error while connecting to db', 0, $e);
                }
        };

        $this['value_storage'] = function () {
            return new ValueStorage($this->getCache());
        };

        $this['db'] = function () {
            return new DatabaseProvider($this['adapter']);
        };

        $this['cacheMemory'] = function () {
            $cache = new CacheProvider();
            $cache->init($this['cache_config']);
            $cache->connect();

            return $cache;
        };

        $this['cacheSession'] = function () {
            return new Session();
        };

        $this['identityMap'] = function () {
            return new IdentityMap(new IdentityMapStorage(), new IdentityMapStorage(), new IdentityMapStorage());
        };

        $this['transaction'] = function () {
            $transaction = new TransactionAggregator();
            foreach ($this['transaction_engines'] as $engine) {
                $transaction->registerTransactionEngine($engine);
            }

            return $transaction;
        };

        $this['transaction_engines'] = function () {
            return [
                $this->getDb(),
                $this->getCache()->getAdapter(),
                $this->getIdentityMap()
            ];
        };

        $this['repository'] = function () {
            return new Repository();
        };

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
     * @return Config
     */
    public function getConfigDb()
    {
        return $this['db_config'];
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
     * @return TransactionAggregator
     */
    public function getTransaction()
    {
        return $this['transaction'];
    }

    /**
     * @return ValueStorage
     */
    public function getValueStorage()
    {
        return $this['value_storage'];
    }
}