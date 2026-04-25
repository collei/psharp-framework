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
        $this->registerSession();
        $this->startSession();
    }

    /**
     * Register the session.
     * 
     * @return void
     */
    protected function registerSession()
    {
        $name = $this->config->get('session.name', 'psharp_session');
        $path = path('storage','framework','sessions');

        $this->container->configureBuilder(Session::class, function(SessionHandlerInterface $handler) use ($name) {
            session_set_save_handler($handler, true);
            Session::setname($name);
            Session::start();
            return Session::getInstance();
        });

        $this->container->configureBuilder(FileSessionHandler::class, function() use ($path, $name) {
            return new FileSessionHandler($path, $name);
        });

        $this->container->addInterfaceImplementor(FileSessionHandler::class, SessionHandlerInterface::class);
    }

    /**
     * Start the session.
     * 
     * @return void
     */
    protected function startSession()
    {
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