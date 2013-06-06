<?php

/**
 * Simplify common operations on counters
 */
abstract class SFM_Counter extends SFM_Value
{
    /**
     * Increment counter by 1
     *
     * @return integer
     */
    public function increment()
    {
        $value = SFM_Cache_Memory::getInstance()->incrementRaw($this->getCacheKey());
        if (false === $value) {
            $value = $this->load();
            ++$value;
            $this->set($value);
        } else {
            $this->value = $value;
        }

        return $this->value;
    }

    /**
     * Decrement counter by 1
     *
     * @return integer
     */
    public function decrement()
    {
        $value = SFM_Cache_Memory::getInstance()->decrementRaw($this->getCacheKey());
        if (false === $value) {
            $value = $this->load();
            --$value;
            $this->set($value);
        } else {
            $this->value = $value;
        }

        return $this->value;
    }

    /**
     * Get value
     *
     * @return integer
     */
    public function get()
    {
        return (int) parent::get();
    }
}
