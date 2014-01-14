<?php
namespace SFM\Cache\Driver;

class DummyDriver implements DriverInterface
{
    /**
     * @param string $host
     * @param string $port
     * @param int $weight
     * @return bool
     */
    public function addServer($host, $port, $weight = 0)
    {
        return true;
    }

    /**
     * @param int $delay
     * @return bool
     */
    public function flush($delay = 0)
    {
        return true;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function get($key)
    {
        return false;
    }

    /**
     * @param array $keys
     * @return bool
     */
    public function getMulti(array $keys)
    {
        return false;
    }

    /**
     * @param array $items
     * @param null|int $expiration
     * @return bool
     */
    public function setMulti(array $items, $expiration = null)
    {
        return true;
    }

    /**
     * @param string $key
     * @param string $value
     * @param int|null $expiration
     * @return bool
     */
    public function set($key, $value, $expiration = null)
    {
        return true;
    }

    /**
     * @param string $key
     * @param int $time
     * @return bool
     */
    public function delete($key, $time = 0)
    {
        return true;
    }

    /**
     * @return int
     */
    public function getResultCode()
    {
        return 0;
    }
}