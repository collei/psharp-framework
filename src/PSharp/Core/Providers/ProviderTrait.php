<?php
namespace PSharp\Core\Providers;

use PSharp\Core\Container;
use LogicException;

/**
 * Encapsulates a service provider.
 */
trait ProviderTrait
{
    /**
     * @var PSharp\Core\Container
     */
    protected $container;

    /**
     * @var PSharp\Core\Config
     */
    protected $config;

    /**
     * @var bool
     */
    private $registered = false;

    /**
     * @var bool
     */
    private $booted = false;

    /**
     * For use of PHP debug functions.
     * 
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'container' => get_instance_id($this->container),
            'config' => get_instance_id($this->config),
            'registered' => ($this->registered ? 'yes' : 'no'),
            'booted' => ($this->booted ? 'yes' : 'no'),
        ];
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
     * Define this provider as registered.
     * 
     * @return void
     */
    public function setRegistered()
    {
        $this->registered = true;
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
     * Define this provider as booted.
     * 
     * @return void
     */
    public function setBooted()
    {
        $this->booted = true;
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