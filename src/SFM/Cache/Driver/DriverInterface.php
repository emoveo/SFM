<?php
namespace SFM\Cache\Driver;

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
     * @param int $delay
     * @return bool
     */
    public function flush($delay = 0);

    /**
     * @param string|$key
     * @return false|mixed
     */
    public function get($key);

    /**
     * @param array $keys
     * @return bool
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
     * @param int $time
     * @return bool
     */
    public function delete($key, $time = 0);

    /**
     * @return int
     */
    public function getResultCode();
}