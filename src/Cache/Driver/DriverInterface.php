<?php
namespace SFM\Cache\Driver;

/**
 * Interface DriverInterface
 * @package SFM\Cache\Driver
 */
interface DriverInterface
{
    /**
     * @param string $host
     * @param string $port
     * @param int $weight
     * @return bool
     */
    public function addServer($host, $port, $weight = 0);

    /**
     * @return bool
     */
    public function flush();

    /**
     * @param string|$key
     * @return false|mixed
     */
    public function get($key);

    /**
     * @param array $keys
     * @return array
     */
    public function getMulti(array $keys);

    /**
     * @param array $items
     * @param null|int $expiration
     * @return bool
     */
    public function setMulti(array $items, $expiration = null);

    /**
     * @param string $key
     * @param string $value
     * @param int|null $expiration
     * @return bool
     */
    public function set($key, $value, $expiration = null);

    /**
     * @param string $key
     * @return bool
     */
    public function delete($key);
}