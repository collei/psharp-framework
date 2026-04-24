<?php
namespace PSharp\Auth;

use PSharp\Core\Application;
use PSharp\Core\Config;
use PSharp\Core\Providers\ServiceProvider;

/**
 * Encapsulates a service provider.
 */
class AuthProvider extends ServiceProvider
{
    /**
     * @var PSharp\Log\AuthManager
     */
    protected $manager;

    /**
     * Registers the services with the container.
     * 
     * @return void
     */
    public function register()
    {
        $this->container->configureBuilder(AuthManager::class, function(Application $app, Config $config){
            return new AuthManager($app, $config);
        });
    }

    /**
     * Boots the services within the container.
     * 
     * @return void
     */
    public function boot()
    {
        $this->manager = $this->container->make(AuthManager::class);
    }
}