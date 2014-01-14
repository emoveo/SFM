<?php
namespace SFM\Cache\Packer;

interface PackerInterface
{
    /**
     * @param mixed $object
     * @return string
     */
    public function pack($object);

    /**
     * @param string $rawData
     * @return array|null
     */
    public function unpack($rawData);

    /**
     * @param string[] $keys
     * @return mixed
     */
    public function resetTags($keys);
}