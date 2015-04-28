<?php
namespace SFM\Value;

interface ValueInterface
{
    /**
     * @return string
     */
    public function getKey();

    /**
     * @return string
     */
    public function getValue();

    /**
     * @return int
     */
    public function getExpiration();
} 