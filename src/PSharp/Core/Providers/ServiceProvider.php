<?php
namespace PSharp\Core\Providers;

use PSharp\Core\Container;
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
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
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