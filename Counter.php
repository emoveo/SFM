<?php

/**
 * Simplify common operations on counters
 * 
 * @author andry
 */
abstract class SFM_Counter extends SFM_Value_Abstract
{
    
    public  function increment()
    {
        $val = SFM_Cache_Memory::getInstance()->incrementRaw($this->getCacheKey());
        if( false === $val ) {
            $val= $this->load();
            ++$val;
            $this->set($val);
        } else {
            $this->value = $val;
        }
        return $this->value;
    }
    
    public  function decrement()
    {
        $val = SFM_Cache_Memory::getInstance()->decrementRaw($this->getCacheKey());
        if( false === $val ) {
            $val= $this->load();
            --$val;
            $this->set($val);
        } else {
            $this->value = $val;
        }
        return $this->value;
    }

    /**
     * @return integer
     */
    public function get()
    {
        return (int) parent::get();
    }
}
