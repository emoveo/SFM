<?php
namespace SFM\Cache\Driver;

/**
 * Class MemcachedDriver
 * @package SFM\Cache\Driver
 */
class MemcachedDriver implements DriverInterface
{
    const DRIVER = 'memcached';

    /** @var \Memcached */
    protected $memcached;

    /**
     * @param \Memcached $memcached
     */
    public function __construct($memcached)
    {
        $this->memcached = $memcached;
    }

    /**
     * @param string $host
     * @param string $port
     * @param int $weight
     * @return bool
     */
    public function addServer($host, $port, $weight = 0)
    {
        return $this->memcached->addServer($host, $port, $weight);
    }

    /**
     * @return bool
     */
    public function flush()
    {
        return $this->memcached->flush();
    }

    /**
     * @param string $key
     * @return false
     */
    public function get($key)
    {
        $value = $this->memcached->get($key);
        return $value === false ? null : $value;
    }

    /**
     * @param array $keys
     * @return bool
     */
    public function getMulti(array $keys)
    {
        $values = $this->memcached->getMulti($keys);
        return false === $values ? [] : $values;
    }

    /**
     * @param array $items
     * @param null|int $expiration
     * @return bool
     */
    public function setMulti(array $items, $expiration = null)
    {
        return $this->memcached->setMulti($items, $expiration);
    }

    /**
     * @param string $key
     * @param string $value
     * @param int|null $expiration
     * @return bool
     */
    public function set($key, $value, $expiration = null)
    {
        return $this->memcached->set($key, $value, $expiration);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        return $this->memcached->delete($key);
    }
}