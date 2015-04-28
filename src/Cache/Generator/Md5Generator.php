<?php
namespace SFM\Cache\Generator;

/**
 * Class Md5Generator
 * @package SFM\Cache\Generator
 */
class Md5Generator implements GeneratorInterface
{
    /** @var string */
    protected $namespace;

    /**
     * @param string $prefix
     */
    public function __construct($prefix)
    {
        $this->namespace = $prefix;
    }

    /**
     * @param string $key
     * @return string
     */
    public function generate($key)
    {
        return md5(md5($this->namespace).md5($key));
    }
}