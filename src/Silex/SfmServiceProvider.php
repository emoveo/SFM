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
        $app['sfm'] = function () {
            return Manager::getInstance();
        };

        $app['sfm']['db_config'] = function () use ($app) {
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
        };

        $app['sfm']['cache_config'] = function () use ($app) {
            $configCache = new \SFM\Cache\Config();
            if (isset($app["sfm.cache"])) {
                $configCache->setHost($app["sfm.cache"]["hostname"])
                    ->setDriver($app["sfm.cache"]["driver"])
                    ->setIsDisabled($app["sfm.cache"]["disabled"])
                    ->setPort($app["sfm.cache"]["port"])
                    ->setPrefix($app["sfm.cache"]["prefix"]);
            } else {
                $configCache->setIsDisabled(true);
            }

            return $configCache;
        };


    }

    public function boot(Application $app)
    {

    }
}