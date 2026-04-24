<?php
namespace PSharp\Http\Sessions;

use PSharp\Core\Application;
use PSharp\Core\Config;
use PSharp\Core\Providers\ServiceProvider;

/**
 * Encapsulates a service provider.
 */
class SessionProvider extends ServiceProvider
{
    /**
     * Registers the services with the container.
     * 
     * @return void
     */
    public function register()
    {
        $this->container->configureBuilder(Session::class, function(){
            return Session::getInstance();
        });
    }

    /**
     * Boots the services within the container.
     * 
     * @return void
     */
    public function boot()
    {
        Session::getInstance()->startSession();
    }
}