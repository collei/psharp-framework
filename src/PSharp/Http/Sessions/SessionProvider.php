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
        $this->startSession();
    }

    /**
     * Start the session.
     * 
     * @return void
     */
    protected function startSession()
    {
        $name = $this->config->get('session.name', 'psharp_session');

        $this->container->configureBuilder(Session::class, function() use ($name) {
            Session::setname($name);
            Session::start();
            return Session::getInstance();
        });

        $this->container->make(Session::class);
    }

    /**
     * Boots the services within the container.
     * 
     * @return void
     */
    public function boot()
    {
        //
    }
}