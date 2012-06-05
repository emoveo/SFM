<?php
require_once 'SFM/Cache.php';
/**
 * Memcache Fake 
 *
 */
class SFM_Cache_Dummy
{
    protected $storage;
    
    public function __construct()
    {
        ;
    }


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
    
    public function set($val)
    {
        ; 
    }
    
    public function setMulti(array $items, $expiration=0)
    {
        foreach ( $items as $tmp) {
            $this->storage[$tmp->getCacheKey()] = $tmp;
        }
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
    
    public function getResultCode()
    {
    	return Memcached::RES_SUCCESS;
    }
    
    public function getVal( $key )
    {
        return $this->storage[$key];
    }
    
    public function getStorage()
    {
        return $this->storage;
    }
}