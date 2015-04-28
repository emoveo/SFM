<?php
namespace SFM\Value;

use SFM\BaseException;

class Value implements ValueInterface
{
    /** @var int */
    protected $expiration;

    /** @var callable */
    protected $function;

    /** @var string */
    protected $key;

    /**
     * @param callable $function
     * @param string $key
     * @param int $expiration
     * @throws BaseException
     */
    public function __construct($function, $key, $expiration = 0)
    {
        if (!is_callable($function)) {
            throw new BaseException("Argument `function` must be callable");
        }

        $this->function = $function;
        $this->key = $key;
        $this->expiration = $expiration;
    }

    /**
     * @return int
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed|string
     */
    public function getValue()
    {
        return call_user_func($this->function);
    }
} 