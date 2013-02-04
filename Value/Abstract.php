<?php

/**
 * Simplify common operations on values
 *
 * @author andry
 */
abstract class SFM_Value_Abstract implements SFM_Transaction_Restorable
{
    protected $value;
    protected $expiration = 0;
    protected $objectState;
    
    /**
     * Load from storage 
     */
    protected abstract function load();

    public function restoreObjectState($state)
    {
        $this->value = $state;
    }

    public function getObjectState()
    {
        return $this->objectState;
    }

    public function getObjectIdentifier()
    {
        return $this->getCacheKey();
    }
    
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
    
    protected function set( $value )
    {
        $this->objectState = $this->value;
        $this->value = $value;
        SFM_Cache_Memory::getInstance()->setValue($this->getCacheKey(), $this, $this->expiration);
        return $this->value;
    }
    

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

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->get();
    }
    
}