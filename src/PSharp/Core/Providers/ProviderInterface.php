<?php
namespace PSharp\Core\Providers;

/**
 * Service provider interface.
 */
interface ProviderInterface 
{
    /**
     * Tells if the services were registered.
     * 
     * @return bool
     */
    public function registered(): bool;

    /**
     * Tells if the services were booted.
     * 
     * @return bool
     */
    public function booted(): bool;

    /**
     * Registers the services with the container.
     * 
     * @return void
     */
    public function register();

    /**
     * Boots the services within the container.
     * 
     * @return void
     */
    public function boot();
}