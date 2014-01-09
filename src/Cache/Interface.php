<?php

interface SFM_Cache_Interface
{
    /**
     * Get value by key
     *
     * @param string $key Key
     * @return mixed
     */
    public function get($key);

    /**
     * Get values by keys
     *
     * @param array $keys Keys
     * @return array|null
     */
    public function getMulti(array $keys);

    /**
     * Set `SFM_Business` value
     *
     * @param SFM_Business $value Object
     */
    public function set(SFM_Business $value);

    /**
     * Set raw value by key
     *
     * @param string $key Key
     * @param mixed $value Value
     * @param int $expiration Expiration
     * @return bool
     */
    public function setRaw($key, $value, $expiration = 0);

    /**
     * Set `SFM_Business` values
     * @param SFM_Business[] $items Objects
     * @param int $expiration Expiration
     */
    public function setMulti(array $items, $expiration = 0);

    /**
     * Delete value by key
     *
     * @param string $key Key
     * @return bool
     */
    public function delete($key);

    /**
     * Flush cache
     */
    public function flush();
}