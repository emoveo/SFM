<?php
namespace SFM\Cache\Driver;

class MemcachedDriver implements DriverInterface
{
    protected $memcached;

    public function __construct()
    {
        $this->memcached = new \Memcached();
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
     * @param int $delay
     * @return bool
     */
    public function flush($delay = 0)
    {
        return $this->memcached->flush($delay);
    }

    /**
     * @param string $key
     * @return false
     */
    public function get($key)
    {
        return $this->memcached->get($key);
    }

    /**
     * @param array $keys
     * @return bool
     */
    public function getMulti(array $keys)
    {
        return $this->memcached->getMulti($keys);
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
     * @param int $time
     * @return bool
     */
    public function delete($key, $time = 0)
    {
        return $this->memcached->delete($key, $time);
    }

    /**
     * @return int
     */
    public function getResultCode()
    {
        return $this->memcached->getResultCode();
    }
}