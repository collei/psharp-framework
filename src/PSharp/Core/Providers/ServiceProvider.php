<?php
namespace PSharp\Core\Providers;

use PSharp\Core\{Container, Config};
use LogicException;

/**
 * Encapsulates a service provider.
 */
abstract class ServiceProvider implements ProviderInterface
{
    use ProviderTrait;

    /**
     * Initializes the provider.
     * 
     * @param PSharp\Core\Container $container
     * @param PSharp\Core\Config $config
     */
    public function __construct(Container $container, Config $config)
    {
        $this->container = $container;
        $this->config = $config;
    }

    /**
     * Registers the services with the container.
     * 
     * @return void
     */
    abstract public function register();

    /**
     * Boots the services within the container.
     * 
     * @return void
     */
    abstract public function boot();
}