<?php
namespace PSharp\Core\Providers;

use PSharp\Core\Container;
use LogicException;

/**
 * Encapsulates a service provider.
 */
abstract class ServiceProvider
{
    /**
     * @var PSharp\Core\Container
     */
    protected $container;

    /**
     * @var bool
     */
    private $registered = false;

    /**
     * @var bool
     */
    private $booted = false;

    /**
     * @var array
     */
    private const INTERCEPTED_METHODS = ['register','boot'];

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
     * Intercepts calls to non-public methods.
     * 
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        if (in_array($method, ServiceProvider::INTERCEPTED_METHODS, true)) {
            if ('boot' == $method && ! $this->registered())  {
                throw new LogicException('Not possible to call boot() on a ServiceProvider before calling register().');
            }

            $value = call_user_func_array([$this, $method], $arguments);

            if ('register' == $method) {
                $this->registered = true;
            } elseif ('boot' == $method) {
                $this->booted = true;
            }

            return $value;
        }
    }

    /**
     * Tells if the services were registered.
     * 
     * @return bool
     */
    public function registered(): bool
    {
        return $this->registered;
    }

    /**
     * Tells if the services were booted.
     * 
     * @return bool
     */
    public function booted(): bool
    {
        return $this->booted;
    }

    /**
     * Registers the services with the container.
     * 
     * @return void
     */
    abstract protected function register();

    /**
     * Boots the services within the container.
     * 
     * @return void
     */
    abstract protected function boot();
}