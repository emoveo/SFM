<?php
namespace SFM\Cache\Generator;

use SFM\Cache\Config;

class Md5Generator implements GeneratorInterface
{
    /** @var Config */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param string|string[] $keys
     * @return string|string[]
     */
    public function generate($keys)
    {
        if (is_array($keys)) {
            $newKeys = array();
            foreach ($keys as $key) {
                $newKeys[] = $this->generate($key);
            }
        } else {
            $newKeys = md5($this->config->getPrefix().$keys);
        }

        return $newKeys;
    }
}