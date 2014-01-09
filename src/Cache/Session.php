<?php

/**
 *  Class for work with Sessions in memory. It is just like SFM_Cache_Memory
 */
class SFM_Cache_Session extends SFM_Cache implements SFM_Cache_Interface
{
    public function setRaw($key,$value,$expiration = 0)
    {
        return $this->driver->set($this->generateKey($key), $value, $expiration);
    }
    
    public function getRaw($key)
    {
        $value = $this->driver->get($this->generateKey($key));
        return ($value === false) ? null : $value;
    }
    
    public function deleteRaw($key)
    {
        return $this->driver->delete($this->generateKey($key));
    }
}    