<?php
namespace SFM\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;
use SFM\Database\Config;
use SFM\Manager;

class SfmServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['sfm'] = $app->share(function () {
            return Manager::getInstance();
        });

        $app['sfm']['db_config'] = $app['sfm']->share(function () use ($app) {
            $sqlConfig = new Config();
            $sqlConfig->setHost($app["sfm.db"]["hostname"])
                      ->setUser($app["sfm.db"]["username"])
                      ->setPass($app["sfm.db"]["password"])
                      ->setDb($app["sfm.db"]["database"])
                      ->setDriver($app["sfm.db"]["driver"]);

            if (isset($app['sfm.db']['queries']) && is_array($app['sfm.db']['queries'])) {
                $sqlConfig->setInitialQueries($app['sfm.db']['queries']);
            }

            return $sqlConfig;
        });

        $app['sfm']['cache_config'] = $app['sfm']->share(function () use ($app) {
            $configCache = new \SFM\Cache\Config();
            $configCache->setHost($app["sfm.cache"]["hostname"])
                        ->setIsDisabled($app["sfm.cache"]["disabled"])
                        ->setPort($app["sfm.cache"]["port"])
                        ->setPrefix($app["sfm.cache"]["prefix"]);

            return $configCache;
        });


    }

    public function boot(Application $app)
    {

    }
}