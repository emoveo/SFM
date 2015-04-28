<?php
namespace SFM\Cache;

use SFM\Cache\Driver\DriverInterface;
use SFM\Transaction\TransactionEngineInterface;
use SFM\Transaction\TransactionException;

/**
 * Class Adapter
 * @package SFM\Cache
 */
class Adapter implements DriverInterface, TransactionEngineInterface
{
    /** @var DriverInterface */
    public $driver;

    /** @var bool */
    protected $isTransaction = false;

    /** @var string[] */
    protected $toAdd = [];

    /** @var string[] */
    protected $toRemove = [];

    /**
     * @param DriverInterface $driver
     */
    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param string $host
     * @param string $port
     * @param int $weight
     * @return bool
     * @throws TransactionException
     */
    public function addServer($host, $port, $weight = 0)
    {
        if ($this->isTransaction) {
            throw new TransactionException("Can't `addServer` while in transaction");
        }

        return $this->driver->addServer($host, $port, $weight);
    }

    /**
     * @return bool
     * @throws TransactionException
     */
    public function flush()
    {
        if ($this->isTransaction) {
            throw new TransactionException("Can't `flush` while in transaction");
        }

        $result = $this->driver->flush();

        return $result;
    }

    /**
     * @param string $key
     * @return false|mixed
     */
    public function get($key)
    {
        $value = null;
        if ($this->isTransaction) {
            if (isset($this->toAdd[$key])) {
                $value = $this->toAdd[$key];
            }
        }

        if (empty($value) && !isset($this->toRemove[$key])) {
            $value = $this->driver->get($key);
        }

        return $value;
    }

    /**
     * @param array $keys
     * @return array
     */
    public function getMulti(array $keys)
    {
        $values = array();
        if ($this->isTransaction) {
            foreach ($keys as $key) {
                if (isset($this->toAdd[$key])) {
                    $values[$key] = $this->toAdd[$key];
                }
            }
        }

        // remove from query keys belong to deleted in transaction values
        $keysFromCache = array_merge($keys);
        $keysFromCache = array_diff($keysFromCache, array_keys($this->toRemove));

        $values = array_merge($this->driver->getMulti($keysFromCache), $values);

        return $values;
    }

    /**
     * @param array $items
     * @param null|int $expiration
     * @return bool
     */
    public function setMulti(array $items, $expiration = null)
    {
        if ($this->isTransaction) {
            foreach ($items as $key => $value) {
                if (isset($this->toRemove[$key])) {
                    unset($this->toRemove[$key]);
                }
                $this->toAdd[$key] = $value;
            }
            $result = true;
        } else {
            $result = $this->driver->setMulti($items, $expiration);
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
        if ($this->isTransaction) {
            $this->toAdd[$key] = $value;
            if (isset($this->toRemove[$key])) {
                unset($this->toRemove[$key]);
            }
            $result = true;
        } else {
            $result = $this->driver->set($key, $value, $expiration);
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
        if ($this->isTransaction) {
            if (isset($this->toAdd[$key])) {
                unset($this->toAdd[$key]);
            }
            $this->toRemove[$key] = $key;
            $result = true;
        } else {
            $result = $this->driver->delete($key);
        }

        return $result;
    }

    public function beginTransaction()
    {
        if ($this->isTransaction === true) {
            throw new TransactionException("Can't begin transaction while another one is running");
        }

        $this->isTransaction = true;
    }

    public function commitTransaction()
    {
        if ($this->isTransaction === false) {
            throw new TransactionException("Can't commit transaction while no one is running");
        }

        $this->isTransaction = false;

        foreach ($this->toAdd as $key => $value) {
            $this->driver->set($key, $value);
        }

        foreach ($this->toRemove as $key => $value) {
            $this->driver->delete($key);
        }

        $this->toAdd = [];
        $this->toRemove = [];
    }

    public function rollbackTransaction()
    {
        if ($this->isTransaction === false) {
            throw new TransactionException("Can't rollback transaction while no one is running");
        }

        $this->isTransaction = false;

        $this->toAdd = [];
        $this->toRemove = [];
    }

    /**
     * @return bool
     */
    public function isTransaction()
    {
        return $this->isTransaction;
    }
}