<?php
namespace SFM;

/**
 * Simplify common operations on counters
 */
abstract class Counter extends Value
{
    /**
     * Increment counter by 1
     *
     * @return integer
     */
    public function increment()
    {
        $value = $this->load();
        ++$value;
        $this->set($value);

        return $this->value;
    }

    /**
     * Decrement counter by 1
     *
     * @return integer
     */
    public function decrement()
    {
        $value = $this->load();
        --$value;
        $this->set($value);

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
