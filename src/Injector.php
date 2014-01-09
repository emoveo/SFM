<?php
/**
 * Injects DIC into SFM objects
 */
class SFM_Injector
{
    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    protected static $container;

    /**
     * Save container for injection
     * @param Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public static function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container = null)
    {
        self::$container = $container;
    }

    /**
     * Injects DIC into matching SFM object
     * @param Symfony\Component\DependencyInjection\ContainerAwareInterface $object
     * @return Symfony\Component\DependencyInjection\ContainerAwareInterface
     */
    public static function inject($object)
    {
        if (isset(self::$container) && ($object instanceof \Symfony\Component\DependencyInjection\ContainerAwareInterface)) {
            $object->setContainer(self::$container);
        }

        return $object;
    }
}