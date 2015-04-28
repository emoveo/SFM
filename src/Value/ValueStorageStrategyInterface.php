<?php
namespace SFM\Value;

interface ValueStorageStrategyInterface
{
    /**
     * @param string $rawKey
     * @return mixed|null
     */
    public function getRaw($rawKey);

    /**
     * @param string $rawKey
     * @param mixed $value
     * @param int $expiration
     * @return bool
     */
    public function setRaw($rawKey, $value, $expiration = 0);

    /**
     * @param string $rawKey
     * @return bool
     */
    public function deleteRaw($rawKey);
} 