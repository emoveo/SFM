<?php
namespace SFM\Value;

class ValueStorage
{
    /** @var ValueStorageStrategyInterface */
    protected $storageStrategy;

    /**
     * @param ValueStorageStrategyInterface $storageStrategy
     */
    public function __construct(ValueStorageStrategyInterface $storageStrategy)
    {
        $this->storageStrategy = $storageStrategy;
    }

    /**
     * @param ValueInterface $value
     * @return mixed|null
     */
    public function get(ValueInterface $value)
    {
        $data = $this->storageStrategy->getRaw($value->getKey());
        if (null === $data) {
            $data = $value->getValue();
            $this->storageStrategy->setRaw($value->getKey(), $data, $value->getExpiration());
        }

        return $data;
    }

    /**
     * @param ValueInterface $value
     */
    public function flush(ValueInterface $value)
    {
        $this->storageStrategy->deleteRaw($value->getKey());
    }
}