<?php
namespace SFM\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

class SfmServiceProvider implements ServiceProviderInterface
{
    protected $app;

    public function register(Application $app)
    {
        $this->app = $app;

        $app['sfm.service.config-db'] = $app->share(function () use ($app) {
            $sqlConfig = new \SFM_Config_Database();
            $sqlConfig->setDb($app["sfm.db"]["hostname"])
                      ->setUser($app["sfm.db"]["username"])
                      ->setPass($app["sfm.db"]["password"])
                      ->setDb($app["sfm.db"]["database"])
                      ->setDriver($app["sfm.db"]["driver"])
                      ->setInitialQueries(array(
                          'SET NAMES utf8'
                      ));

            return $sqlConfig;
        });

        $app['sfm.service.config-cache'] = $app->share(function () use ($app) {
            $configCache = new \SFM\Cache\Config();
            $configCache->setHost($app["sfm.cache"]["hostname"])
                        ->setIsDisabled($app["sfm.cache"]["disabled"])
                        ->setPort($app["sfm.cache"]["port"])
                        ->setPrefix($app["sfm.cache"]["prefix"]);

            return $configCache;
        });

        $app['sfm'] = $app->share(function () {
            return \SFM_Manager::getInstance();
        });
    }

    public function boot(Application $app)
    {
        $app['sfm']->getDb()->init($app['sfm.service.config-db'])->connect();
        $app['sfm']->getCache()->init($app['sfm.service.config-cache'])->connect();
    }
}