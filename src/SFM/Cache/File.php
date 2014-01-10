<?php

class SFM_Cache_File extends SFM_Cache 
{
    public function setMulti(array $items, $expiration=0)
    {
        throw new SFM_Exception_Memcached('Method setMulti is not implemented for memcachedb');
    }
    
    /**
     * Wrapper over Memcached get method
     * If the cache is down, throws an exception
     * 
     * @param string $key
     * @return mixed|null
     * @throws SFM_Exception_Memcached
     */
    protected function _get($key)
    {
        $value = $this->driver->get($this->generateKey($key));
        $returnValue = ($value === false) ? null : $value;
        if(($returnValue == null) && ($this->driver->getResultCode() != Memcached::RES_NOTFOUND))
            throw new SFM_Exception_Memcached('Server is down');
        else 
            return $returnValue;    
    }
}    