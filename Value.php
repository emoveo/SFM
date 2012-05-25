<?php
require_once 'SFM/Cache/Memory.php';

/**
 * Simplify common operations on counters
 *
 * @author andry
 */
abstract class SFM_Value
{
    protected $value;
    
    public function get()
    {
        if( !isset($this->value) ) {
            $value = SFM_Cache_Memory::getInstance()->get($this->getCacheKey());
            if( null !== $value ) {
                $this->value = $value;
            } else {
                $this->value = $this->load();
                SFM_Cache_Memory::getInstance()->set($this->getCacheKey(), $this->value);
            }
        }
        return $this->value;
    }
    
    public function set( $value )
    {
        $this->value = $value;
        SFM_Cache_Memory::getInstance()->set($this->getCacheKey(), $this->value);
    }

    
    protected abstract function getCacheKey();
    
    /**
     * Load from storage 
     */
    protected abstract function load();

    /**
     *
     * @param array of SFM_Business $dependency 
     * @return string
     */
    protected function getCacheKeyBy(array $dependency)
    {
        $key = get_class($this) . SFM_Cache_Memory::KEY_DILIMITER;
        
        foreach ($dependency as $item) {
           $key .= $item->getCacheKey();
        }
        return $key;
    }    
}