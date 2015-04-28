<?php
namespace SFM\Cache\Driver;

/**
 * Class DummyDriver
 * @package SFM\Cache\Driver
 */
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
     * @return bool
     */
    public function flush()
    {
        return true;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function get($key)
    {
        return null;
    }

    /**
     * @param array $keys
     * @return bool
     */
    public function getMulti(array $keys)
    {
        return [];
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
     * @return bool
     */
    public function delete($key)
    {
        return true;
    }
}