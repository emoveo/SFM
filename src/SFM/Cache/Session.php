<?php

/**
 *  Class for work with Sessions in memory. It is just like SFM_Cache_Memory
 */
class SFM_Cache_Session extends SFM_Cache implements SFM_Cache_Interface
{
    public function setRaw($key,$value,$expiration = 0)
    {
        return $this->driverCache->set($this->generateKey($key), $value, $expiration);
    }
    
    public function getRaw($key)
    {
        $value = $this->driverCache->get($this->generateKey($key));
        return ($value === false) ? null : $value;
    }
    
    public function deleteRaw($key)
    {
        return $this->driverCache->delete($this->generateKey($key));
    }
}    