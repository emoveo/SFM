<?php
require_once 'SFM/Cache/Memory.php';
require_once 'SFM/Value.php';

/**
 * Simplify common operations on counters
 *
 * @author andry
 */
abstract class SFM_Counter extends SFM_Value
{
    
    public  function increment()
    {
        $val = SFM_Cache_Memory::getInstance()->incrementRaw($this->getCacheKey());
        if( false === $val ) {
            $this->value = $this->load();
            ++$this->value;
            SFM_Cache_Memory::getInstance()->set($this->getCacheKey(), $this->value);
        }
        
    }
    
    public  function decrement()
    {
        $val = SFM_Cache_Memory::getInstance()->decrementRaw($this->getCacheKey());
        if( false === $val ) {
            $this->value = $this->load();
            --$this->value;
            SFM_Cache_Memory::getInstance()->set($this->getCacheKey(), $this->value);
        }
    }
}
