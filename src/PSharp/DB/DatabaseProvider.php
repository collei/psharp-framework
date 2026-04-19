<?php
namespace PSharp\DB;

use PSharp\Core\Application;
use PSharp\Core\Config;
use PSharp\Core\Providers\ServiceProvider;

/**
 * Encapsulates a service provider.
 */
class DatabaseProvider extends ServiceProvider
{
    /**
     * @var PSharp\DB\DatabaseManager
     */
    protected $manager;

    /**
     * Registers the services with the container.
     * 
     * @return void
     */
    public function register()
    {
        $this->container->configureBuilder(DatabaseManager::class, function(Application $app, Config $config){
            return new DatabaseManager($app, $config);
        });
    }

    /**
     * Boots the services within the container.
     * 
     * @return void
     */
    public function boot()
    {
        $this->manager = $this->container->make(DatabaseManager::class);
    }
}