<?php

/**
 * Simplify common operations on values
 *
 * @author andry
 */
abstract class SFM_Value
{
    protected $value;
    protected $expiration = 0;
       
    public function get()
    {
        if( !isset($this->value) ) {
            $value = SFM_Cache_Memory::getInstance()->getRaw($this->getCacheKey());
            if( null !== $value ) {
                $this->value = $value;
            } else {
                $this->set($this->load());
            }
        }
        return $this->value;
    }
    
    public function set( $value )
    {
        $this->value = $value;
        SFM_Cache_Memory::getInstance()->setRaw($this->getCacheKey(), $this->value, $this->expiration);
        return $this->value;
    }

    
    protected abstract function getCacheKey();
    
    /**
     * Load from storage 
     */
    protected abstract function load();

    /**
     * @param array of SFM_Business $dependency 
     * @param string $postfix
     * @return string
     */
    protected function getCacheKeyBy(array $dependency, $postfix = '')
    {
        $key = get_class($this) . SFM_Cache_Memory::KEY_DILIMITER;
        
        foreach ($dependency as $item) {
           $key .= $item->getCacheKey();
        }
        return $key.$postfix;
    }
    
    protected function setExpiration($expiration)
    {
        $this->expiration = $expiration;
    }
}