<?php
namespace SFM\Cache\Generator;

interface GeneratorInterface
{
    /**
     * @param mixed $keys
     * @return string|string[]
     */
    public function generate($keys);
}