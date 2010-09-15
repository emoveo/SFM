<?php
/**
 * Memcache Fake 
 *
 */
class SFM_Cache_Dummy
{
    public function addServer($host, $port)
    {
        return true;
    }
    public function get($key)
    {
        return FALSE;
    }
    public function getMulti(array $keys)
    {
        return FALSE;
    }
    
    public function set($key, $val, $expiration=0)
    {
        ; 
    }
    
    public function setMulti(array $items, $expiration=0)
    {
        ;
    }
    
    public function delete($key)
    {
        ;
    }
    public function flush()
    {
        ;
    }
    public function setOption($fakeKey, $fakeBool)
    {
        ;
    }
}