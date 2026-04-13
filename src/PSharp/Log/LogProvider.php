<?php
namespace PSharp\Log;

use PSharp\Core\Application;
use PSharp\Core\Config;
use PSharp\Core\Providers\ServiceProvider;

/**
 * Encapsulates a service provider.
 */
class LogProvider extends ServiceProvider
{
    /**
     * @var PSharp\Log\LogManager
     */
    protected $manager;

    /**
     * Registers the services with the container.
     * 
     * @return void
     */
    public function register()
    {
        $this->container->configureBuilder(LogManager::class, function(Application $app, Config $config){
            return new LogManager($app, $config);
        });
    }

    /**
     * Boots the services within the container.
     * 
     * @return void
     */
    public function boot()
    {
        $this->manager = $this->container->make(LogManager::class);
    }
}