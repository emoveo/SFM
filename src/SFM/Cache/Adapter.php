<?php
namespace SFM\Cache;

use SFM\Cache\Driver\DriverInterface;
use SFM\Cache\Driver\DummyDriver;
use SFM\Cache\Driver\MemcachedDriver;
use SFM\Cache\Transaction\TransactionCache;

class Adapter implements DriverInterface
{
    const DUMMY = 1;
    const MEMCACHED = 2;

    const FORCE_TIMEOUT = 1;

    /** @var \SFM\Cache\Driver\DriverInterface */
    protected $driver;

    /** @var \SFM_MonitorInterface */
    protected $monitor;

    /**
     * @param string $driver
     * @param TransactionCache $transactionCache
     */
    public function __construct($driver, TransactionCache $transactionCache)
    {
        $this->driver = $this->createDriver($driver);
        $this->transactionCache = $transactionCache;
    }

    /**
     * @param \SFM_MonitorInterface $monitor
     */
    public function setMonitor(\SFM_MonitorInterface $monitor = null)
    {
        $this->monitor = $monitor;
    }

    /**
     * @param string $driver
     * @return \SFM\Cache\Driver\DriverInterface
     */
    protected function createDriver($driver)
    {
        $engine = null;
        switch ($driver) {
            case Adapter::MEMCACHED:
                $engine = new MemcachedDriver();
                break;
            default:
                $engine = new DummyDriver();
                break;
        }

        return $engine;
    }

    /**
     * @param $time
     */
    protected function checkCacheIsAlive($time)
    {
        // TODO: Rewrite
        if (microtime(true) - $time > Adapter::FORCE_TIMEOUT) {
            $this->driver = $this->createDriver(Adapter::DUMMY);
        }
    }

    /**
     * @param string $host
     * @param string $port
     * @param int $weight
     * @return bool
     */
    public function addServer($host, $port, $weight = 0)
    {
        return $this->driver->addServer($host, $port, $weight);
    }

    /**
     * @param int $delay
     * @return bool
     */
    public function flush($delay = 0)
    {
        if ($this->monitor !== null) {
            $timer = $this->monitor->createTimer(array('db' => get_class($this), 'operation' => 'flush'));
        }

        $time = microtime(true);
        $result = $this->driver->flush($delay);
        $this->checkCacheIsAlive($time);

        if (isset($timer)) {
            $timer->stop();
        }

        return $result;
    }

    /**
     * @param string $key
     * @return false|mixed
     */
    public function get($key)
    {
        if ($this->monitor !== null) {
            $timer = $this->monitor->createTimer(array('db' => get_class($this), 'operation' => 'get'));
        }

        $time = microtime(true);

        $value = null;
        if ($this->transactionCache->isTransaction()) {
            $value = $this->transactionCache->getRaw($key);
        }

        if (empty($value)) {
            $value = $this->driver->get($key);
        }

        $this->checkCacheIsAlive($time);

        if (isset($timer)) {
            $timer->stop();
        }

        return $value;
    }

    /**
     * @param array $keys
     * @return array
     */
    public function getMulti(array $keys)
    {
        $time = microtime(true);

        if ($this->monitor !== null) {
            $timer = $this->monitor->createTimer(array('db' => get_class($this), 'operation' => 'getMulti'));
        }

        $values = array();
        if ($this->transactionCache->isTransaction()) {

            $values = array();
            foreach ($keys as $key) {
                $values[$key] = $this->transactionCache->getRaw($key);
            }
        }

        if (empty($values)) {
            $values = $this->driver->getMulti($keys);
        }

        $this->checkCacheIsAlive($time);

        if (isset($timer)) {
            $timer->stop();
        }

        return $values;
    }

    /**
     * @param array $items
     * @param null|int $expiration
     * @return bool
     */
    public function setMulti(array $items, $expiration = null)
    {
        if ($this->monitor !== null) {
            $timer = $this->monitor->createTimer(array('db' => get_class($this), 'operation' => 'setMulti'));
        }

        $time = microtime(true);

        if ($this->transactionCache->isTransaction()) {
            $result = $this->transactionCache->logRawMulti($items, $expiration);
        } else {
            $result = $this->driver->setMulti($items, $expiration);
        }

        $this->checkCacheIsAlive($time);

        if (isset($timer)) {
            $timer->stop();
        }

        return $result;
    }

    /**
     * @param string $key
     * @param string $value
     * @param int|null $expiration
     * @return bool
     */
    public function set($key, $value, $expiration = null)
    {
        if ($this->monitor !== null) {
            $timer = $this->monitor->createTimer(array('db' => get_class($this->driver), 'operation' => 'set'));
        }

        $time = microtime(true);
        if ($this->transactionCache->isTransaction()) {
            $result = $this->transactionCache->logRaw($key, $value, $expiration);
        } else {
            $result = $this->driver->set($key, $value, $expiration);
        }

        $this->checkCacheIsAlive($time);

        if (isset($timer)) {
            $timer->stop();
        }

        return $result;
    }

    /**
     * @param string $key
     * @param int $time
     * @return bool
     */
    public function delete($key, $time = 0)
    {
        if ($this->monitor !== null) {
            $timer = $this->monitor->createTimer(array('db' => get_class($this), 'operation' => 'delete'));
        }

        $time = microtime(true);
        if ($this->transactionCache->isTransaction()) {
            $result = $this->transactionCache->logDeleted($key);
        } else {
            $result = $this->driver->delete($key);
            $this->checkCacheIsAlive($time);
        }

        if (isset($timer)) {
            $timer->stop();
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getResultCode()
    {
        return $this->driver->getResultCode();
    }

}