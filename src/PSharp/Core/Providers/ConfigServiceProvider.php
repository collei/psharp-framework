<?php
namespace PSharp\Core\Providers;

use PSharp\Core\Container;
use PSharp\Core\Config;

/**
 * Encapsulates a service provider.
 */
class ConfigServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * Initializes the provider.
     * 
     * @param PSharp\Core\Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);

        $this->filePath = './appsettings.json';
    }

    /**
     * Registers the services with the container.
     * 
     * @return void
     */
    protected function register()
    {
        $filePath = $this->filePath;

        $this->container->configureBuilder(Config::class, function() use ($filePath) {
            return new Config($filePath);
        });        
    }

    /**
     * Boots the services within the container.
     * 
     * @return void
     */
    protected function boot()
    {
        $this->make(Config::class);
    }
}