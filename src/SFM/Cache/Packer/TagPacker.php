<?php
namespace SFM\Cache\Packer;

use SFM\Cache\CacheProvider;
use SFM\Cache\Adapter;
use SFM\Cache\Generator\GeneratorInterface;

class TagPacker
{
    protected $adapter;
    protected $generator;

    /**
     * @param Adapter $adapter
     * @param GeneratorInterface $generator
     */
    public function __construct(Adapter $adapter, GeneratorInterface $generator)
    {
        $this->adapter = $adapter;
        $this->generator = $generator;
    }

    /**
     * @param mixed $object
     * @return string
     */
    public function pack($object)
    {
        if ($object instanceof \SFM_Business) {
            $data = array(
                CacheProvider::KEY_VALUE => serialize($object),
                CacheProvider::KEY_TAGS  => $this->getTags($object->getCacheTags()),
                CacheProvider::KEY_EXPIRES  => $object->getExpires(),
            );
            $packed = $this->pack($data);
        } else {
            $packed = serialize($object);
        }

        return $packed;
    }

    /**
     * @param string $rawData
     * @return array|null
     */
    public function unpack($rawData)
    {
        if (is_array($rawData)) {
            $result = array();
            foreach ($rawData as $rawDataItem) {
                $object = $this->unpack($rawDataItem);
                if($object !== null){
                    $result[] = $object;
                }
            }
        } else {
            $data = unserialize($rawData);
            if (false === is_array($data)) {
                $result = null;
            } else {
                $oldTagValues = (array) $data[CacheProvider::KEY_TAGS];

                $newTagValues = $this->getTags(array_keys($oldTagValues));
                //expiration objects should expire without tags
                if ($oldTagValues == $newTagValues || $data[CacheProvider::KEY_EXPIRES]) {
                    $result = unserialize($data[CacheProvider::KEY_VALUE]);
                } else {
                    $result = null;
                }
            }
        }

        return $result;
    }

    /**
     * Get tag values by keys
     *
     * @param array $keys
     * @return array
     */
    protected  function getTags($keys)
    {
        $keys = (array) $keys;
        $values = array();
        $tagKeys = array();
        foreach ($keys as $key) {
            $tagKeys[] = $this->getTagByKey($key);
        }

        $tagValues = $this->adapter->getMulti($tagKeys);

        if (empty($tagValues)) {
            $tagValues = array();
        }

        $i = 0;
        foreach($tagValues as $tagValue) {
            $key = $keys[$i];
            $value = unserialize($tagValue);
            if ( false === $value) {
                $value = $this->resetTags($key);
            }
            $values[$key] = $value;
            $i++;
        }

        return $values;
    }

    /**
     * Returns key for storing tags.
     * Since tag keys must differ from object keys, method concatinates some prefix
     *
     * @param string $key Original name of tag. Can be the same as Entity Cache key
     * @return string
     */
    protected function getTagByKey($key)
    {
        return $this->generator->generate('Tag' . $key);
    }


    /**
     * Resets tag values and returns new values
     * The return type depends on type of $keys
     *
     * @param array $keys
     * @return array
     */
    public function resetTags($keys)
    {
        $keys = (array) $keys;
        $values = array();
        $tagValues = array();
        foreach ($keys as $key) {
            $tag = $this->getTagByKey($key);
            $values [$key]= $value = microtime(true);
            $tagValues[$tag] = serialize($value);
        }
        if(!empty($tagValues)) {

            $resultItems = array();
            foreach($tagValues as $key => $value) {
                $resultItems[$key] = $value;
            }

            $this->adapter->setMulti($resultItems);
        }
        return $values;
    }
}