<?php

/**
 * Simplify common operations on values
 */
abstract class SFM_Value implements SFM_Transaction_Restorable
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var integer
     */
    protected $expiration = 0;

    /**
     * @var mixed
     */
    protected $objectState;
    
    /**
     * Load from storage
     *
     * @return mixed
     */
    protected abstract function load();

    /**
     * @return string
     */
    protected abstract function getCacheKey();

    /**
     * @param array $dependency
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

    /**
     * Transaction API: Restore value state
     *
     * @param mixed $state
     * @return mixed
     */
    public function restoreObjectState($state)
    {
        $this->value = $state;
    }

    /**
     * Transaction API: Get current value state
     *
     * @return mixed
     */
    public function getObjectState()
    {
        return $this->objectState;
    }

    /**
     * Transaction API: Get value unique identifier
     *
     * @return string
     */
    public function getObjectIdentifier()
    {
        return $this->getCacheKey();
    }

    /**
     * Get value
     *
     * @return mixed
     */
    public function get()
    {
        if (false === isset($this->value)) {
            $value = SFM_Cache_Memory::getInstance()->getRaw($this->getCacheKey());
            if (null !== $value) {
                $this->value = $value;
            } else {
                $this->set($this->load());
            }
        }

        return $this->value;
    }

    /**
     * Set value
     *
     * @param mixed $value
     * @return mixed
     */
    protected function set($value)
    {
        $this->objectState = $this->value;
        $this->value = $value;

        SFM_Cache_Memory::getInstance()->setValue($this->getCacheKey(), $this, $this->expiration);

        return $this->value;
    }

    /**
     * Flush value to null
     */
    public function flush()
    {
        $this->objectState = null;
        $this->value = null;

        SFM_Cache_Memory::getInstance()->deleteRaw($this->getCacheKey());
    }

    /**
     * Set value expiration time
     * @param integer $expiration
     */
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