<?php

class SFM_Cache_Dummy
{
    const RES_SUCCESS = 0;

    public function __construct()
    {

    }

    public function addServer($host, $port)
    {
        return true;
    }

    public function get($key)
    {
        return false;
    }

    public function getMulti(array $keys)
    {
        return false;
    }

    public function set($val)
    {

    }

    public function setMulti(array $items, $expiration = 0)
    {

    }

    public function delete($key)
    {

    }
    public function flush()
    {

    }
    public function setOption($fakeKey, $fakeBool)
    {

    }

    public function getResultCode()
    {
        return self::RES_SUCCESS;
    }
}